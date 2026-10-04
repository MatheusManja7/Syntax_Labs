<?php

require_once __DIR__ . '/../../config/database.php';

class Mensagem
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function listar(): array
    {
        return $this->pdo->query(
            'SELECT m.id, m.nome, m.email, m.empresa, m.telefone, m.assunto, m.mensagem, m.lida, m.arquivada, m.criado_em,
                    (SELECT COUNT(*) FROM mensagens_respostas r WHERE r.mensagem_id = m.id) AS respostas,
                    (SELECT MAX(r.enviada_em) FROM mensagens_respostas r WHERE r.mensagem_id = m.id) AS respondida_em
            FROM mensagens m
            ORDER BY m.criado_em DESC'
        )->fetchAll();
    }

    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, nome, email, empresa, telefone, assunto, mensagem, lida, arquivada, criado_em
            FROM mensagens WHERE id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $id]);

        return $stmt->fetch() ?: null;
    }

    public function marcarComoLida(int $id): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE mensagens SET lida = 1, lida_em = NOW()
              WHERE id = :id AND lida = 0'
        );
        $stmt->execute([':id' => $id]);
    }

    public function excluir(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM mensagens WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }
    public function criar(string $nome, string $email, ?string $empresa, ?string $telefone, string $assunto, string $mensagem): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO mensagens (nome, email, empresa, telefone, assunto, mensagem)
            VALUES (:nome, :email, :empresa, :telefone, :assunto, :mensagem)'
        );
        $stmt->execute([
            ':nome'     => $nome,
            ':email'    => $email,
            ':empresa'  => $empresa,
            ':telefone' => $telefone,
            ':assunto'  => $assunto,
            ':mensagem' => $mensagem,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function registrarResposta(int $mensagemId, int $usuarioId, string $resposta): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO mensagens_respostas (mensagem_id, usuario_id, resposta)
            VALUES (:m, :u, :r)'
        );
        $stmt->execute([':m' => $mensagemId, ':u' => $usuarioId, ':r' => $resposta]);
    }

    public function definirArquivada(int $id, bool $arquivar): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE mensagens
                SET arquivada = :a, arquivada_em = IF(:a2 = 1, NOW(), NULL)
            WHERE id = :id'
        );
        $stmt->bindValue(':a', $arquivar ? 1 : 0, PDO::PARAM_INT);
        $stmt->bindValue(':a2', $arquivar ? 1 : 0, PDO::PARAM_INT);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function contarNaoLidas(): int
    {
        return (int) $this->pdo->query(
            'SELECT COUNT(*) FROM mensagens WHERE lida = 0 AND arquivada = 0'
        )->fetchColumn();
    }
}