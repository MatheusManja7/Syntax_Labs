<?php

require_once __DIR__ . '/../../config/database.php';

class Orcamento
{
    public const STATUS = ['novo', 'editando', 'enviado', 'aceito', 'rejeitado'];

    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function listar(): array
    {
        $orcs = $this->pdo->query(
            'SELECT id, status, nome, empresa, email, telefone, tipos, objetivos, descricao,
                    possui, referencias, faixa, inicio, criado_em,
                    prop_titulo, prop_escopo, prop_prazo, prop_valor_min, prop_valor_max,
                    prop_pagamento, prop_validade, prop_obs, enviada_em
               FROM orcamentos
              ORDER BY criado_em DESC'
        )->fetchAll();

        $anexos = $this->pdo->query(
            'SELECT id, orcamento_id, nome_original FROM orcamentos_anexos ORDER BY id'
        )->fetchAll();

        $porOrc = [];
        foreach ($anexos as $a) {
            $porOrc[$a['orcamento_id']][] = ['id' => (int) $a['id'], 'nome' => $a['nome_original']];
        }

        foreach ($orcs as &$o) {
            $o['anexos'] = $porOrc[$o['id']] ?? [];
        }

        return $orcs;
    }

    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM orcamentos WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);

        return $stmt->fetch() ?: null;
    }

    public function contarNovos(): int
    {
        return (int) $this->pdo->query(
            "SELECT COUNT(*) FROM orcamentos WHERE status = 'novo'"
        )->fetchColumn();
    }

    /** "novo" vira "editando" ao ser aberto. Não mexe nos demais status. */
    public function marcarEditando(int $id, int $usuarioId): void
    {
        $stmt = $this->pdo->prepare(
            "UPDATE orcamentos SET status = 'editando', atualizado_por = :u
              WHERE id = :id AND status = 'novo'"
        );
        $stmt->execute([':u' => $usuarioId, ':id' => $id]);
    }

    public function salvarProposta(int $id, array $p, int $usuarioId): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE orcamentos
                SET prop_titulo = :titulo, prop_escopo = :escopo, prop_prazo = :prazo,
                    prop_valor_min = :vmin, prop_valor_max = :vmax,
                    prop_pagamento = :pagto, prop_validade = :validade, prop_obs = :obs,
                    atualizado_por = :u
              WHERE id = :id'
        );
        $stmt->execute([
            ':titulo'   => $p['titulo'],
            ':escopo'   => $p['escopo'],
            ':prazo'    => $p['prazo'],
            ':vmin'     => $p['valor_min'],
            ':vmax'     => $p['valor_max'],
            ':pagto'    => $p['pagamento'],
            ':validade' => $p['validade'] ?: null,
            ':obs'      => $p['obs'],
            ':u'        => $usuarioId,
            ':id'       => $id,
        ]);
    }

    public function reabrir(int $id, int $usuarioId): void
    {
        $stmt = $this->pdo->prepare(
            "UPDATE orcamentos
                SET status = 'editando', token_hash = NULL, respondida_em = NULL, atualizado_por = :u
              WHERE id = :id AND status IN ('aceito','rejeitado')"
        );
        $stmt->execute([':u' => $usuarioId, ':id' => $id]);
    }

    public function excluir(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM orcamentos WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }

    /** Marca como enviado e grava o hash do token. Um novo token invalida o anterior (reenvio). */
    public function marcarEnviado(int $id, string $tokenHash, int $usuarioId): void
    {
        $stmt = $this->pdo->prepare(
            "UPDATE orcamentos
                SET status = 'enviado', token_hash = :h, enviada_em = NOW(),
                    respondida_em = NULL, atualizado_por = :u
            WHERE id = :id AND status IN ('novo','editando','enviado')"
        );
        $stmt->execute([':h' => $tokenHash, ':u' => $usuarioId, ':id' => $id]);
    }

    public function criarComAnexos(array $d, array $arquivos, string $pasta): int
    {
        $movidos = [];
        $this->pdo->beginTransaction();

        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO orcamentos
                    (nome, empresa, email, telefone, tipos, objetivos, descricao, possui, referencias, faixa, inicio)
                VALUES
                    (:nome, :empresa, :email, :telefone, :tipos, :objetivos, :descricao, :possui, :referencias, :faixa, :inicio)'
            );
            $stmt->execute([
                ':nome'        => $d['nome'],
                ':empresa'     => $d['empresa'],
                ':email'       => $d['email'],
                ':telefone'    => $d['telefone'],
                ':tipos'       => $d['tipos'],
                ':objetivos'   => $d['objetivos'],
                ':descricao'   => $d['descricao'],
                ':possui'      => $d['possui'] !== '' ? $d['possui'] : null,
                ':referencias' => $d['referencias'] !== '' ? $d['referencias'] : null,
                ':faixa'       => $d['faixa'] !== '' ? $d['faixa'] : null,
                ':inicio'      => $d['inicio'] !== '' ? $d['inicio'] : null,
            ]);
            $id = (int) $this->pdo->lastInsertId();

            $ins = $this->pdo->prepare(
                'INSERT INTO orcamentos_anexos (orcamento_id, nome_original, nome_arquivo, mime, tamanho)
                VALUES (:o, :nome, :arq, :mime, :tam)'
            );

            foreach ($arquivos as $a) {
                $destino = $pasta . '/' . $a['nome_arquivo'];

                if (!move_uploaded_file($a['tmp'], $destino)) {
                    throw new RuntimeException('Falha ao salvar o anexo.');
                }
                $movidos[] = $destino;

                $ins->execute([
                    ':o'    => $id,
                    ':nome' => $a['nome_original'],
                    ':arq'  => $a['nome_arquivo'],
                    ':mime' => $a['mime'],
                    ':tam'  => $a['tamanho'],
                ]);
            }

            $this->pdo->commit();
            return $id;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            foreach ($movidos as $m) {
                @unlink($m);
            }
            throw $e;
        }
    }

    public function buscarAnexo(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, nome_original, nome_arquivo, mime FROM orcamentos_anexos WHERE id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $id]);

        return $stmt->fetch() ?: null;
    }

    public function buscarPorTokenHash(string $hash): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, status, nome, empresa, email, telefone,
                    prop_titulo, prop_escopo, prop_prazo, prop_valor_min, prop_valor_max,
                    prop_pagamento, prop_validade, prop_obs, enviada_em, respondida_em
            FROM orcamentos WHERE token_hash = :h LIMIT 1'
        );
        $stmt->execute([':h' => $hash]);

        return $stmt->fetch() ?: null;
    }

    /** Só decide se ainda estiver "enviado". Retorna false se alguém já decidiu (evita clique duplo). */
    public function registrarDecisao(int $id, string $status): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE orcamentos SET status = :s, respondida_em = NOW()
            WHERE id = :id AND status = 'enviado'"
        );
        $stmt->execute([':s' => $status, ':id' => $id]);

        return $stmt->rowCount() > 0;
    }
}