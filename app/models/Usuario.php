<?php

require_once __DIR__ . '/../../config/database.php';

class Usuario
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    /**
     * Busca um usuário ativo pelo e-mail.
     * Retorna o array do usuário ou null se não existir.
     */
    public function buscarPorEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, nome, email, senha_hash
               FROM usuarios
              WHERE email = :email AND ativo = 1
              LIMIT 1'
        );
        $stmt->execute([':email' => $email]);

        $usuario = $stmt->fetch();

        return $usuario ?: null;
    }

    /**
     * Busca um usuário ativo pelo ID.
     */
    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, nome, email
               FROM usuarios
              WHERE id = :id AND ativo = 1
              LIMIT 1'
        );
        $stmt->execute([':id' => $id]);

        $usuario = $stmt->fetch();

        return $usuario ?: null;
    }

    /**
     * Confere e-mail + senha.
     * Retorna os dados do usuário (sem o hash) se estiver correto, ou null.
     */
    public function autenticar(string $email, string $senha): ?array
    {
        $usuario = $this->buscarPorEmail($email);

        if ($usuario === null || !password_verify($senha, $usuario['senha_hash'])) {
            return null;
        }

        // Nunca devolva o hash para o resto da aplicação
        unset($usuario['senha_hash']);

        return $usuario;
    }

    /**
     * Atualiza a senha do usuário (usado na recuperação de senha).
     */
    public function atualizarSenha(int $id, string $novaSenha): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE usuarios SET senha_hash = :hash WHERE id = :id'
        );

        return $stmt->execute([
            ':hash' => password_hash($novaSenha, PASSWORD_DEFAULT),
            ':id'   => $id,
        ]);
    }
}