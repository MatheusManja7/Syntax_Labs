<?php

require_once __DIR__ . '/../../config/database.php';

class PasswordReset
{
    private const VALIDADE_MINUTOS = 30;

    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    /**
     * Gera um token para o usuário e grava o HASH dele no banco.
     * Retorna o token em texto puro (que vai no link do e-mail).
     */
    public function criar(int $usuarioId): string
    {
        // Invalida tokens anteriores ainda não usados
        $stmt = $this->pdo->prepare(
            'UPDATE password_resets SET usado = 1
              WHERE usuario_id = :id AND usado = 0'
        );
        $stmt->execute([':id' => $usuarioId]);

        $token     = bin2hex(random_bytes(32));   // 64 caracteres hex
        $tokenHash = hash('sha256', $token);

        $stmt = $this->pdo->prepare(
            'INSERT INTO password_resets (usuario_id, token_hash, expira_em)
             VALUES (:id, :hash, DATE_ADD(NOW(), INTERVAL :min MINUTE))'
        );
        $stmt->bindValue(':id', $usuarioId, PDO::PARAM_INT);
        $stmt->bindValue(':hash', $tokenHash);
        $stmt->bindValue(':min', self::VALIDADE_MINUTOS, PDO::PARAM_INT);
        $stmt->execute();

        return $token;
    }

    /**
     * Confere se o token é válido (existe, não usado, não expirado).
     * Retorna o usuario_id ou null.
     */
    public function validar(string $token): ?int
    {
        $stmt = $this->pdo->prepare(
            'SELECT usuario_id FROM password_resets
              WHERE token_hash = :hash
                AND usado = 0
                AND expira_em > NOW()
              LIMIT 1'
        );
        $stmt->execute([':hash' => hash('sha256', $token)]);

        $id = $stmt->fetchColumn();

        return $id !== false ? (int) $id : null;
    }

    /**
     * Marca o token como usado (chamado após trocar a senha).
     */
    public function marcarComoUsado(string $token): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE password_resets SET usado = 1 WHERE token_hash = :hash'
        );
        $stmt->execute([':hash' => hash('sha256', $token)]);
    }
}