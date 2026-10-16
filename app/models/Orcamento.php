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
}