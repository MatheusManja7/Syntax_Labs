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
    
    public function criar(string $nome, string $email, string $senha): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO usuarios (nome, email, senha_hash)
            VALUES (:nome, :email, :hash)'
        );
        $stmt->execute([
            ':nome'  => $nome,
            ':email' => $email,
            ':hash'  => password_hash($senha, PASSWORD_DEFAULT),
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function listar(): array
    {
        return $this->pdo->query(
            'SELECT id, nome, email, ativo, criado_em FROM usuarios ORDER BY nome'
        )->fetchAll();
    }

    public function existe(int $id): bool
    {
        $stmt = $this->pdo->prepare('SELECT 1 FROM usuarios WHERE id = :id');
        $stmt->execute([':id' => $id]);

        return $stmt->fetchColumn() !== false;
    }

    public function alterarStatus(int $id, bool $ativo): void
    {
        $stmt = $this->pdo->prepare('UPDATE usuarios SET ativo = :ativo WHERE id = :id');
        $stmt->bindValue(':ativo', $ativo ? 1 : 0, PDO::PARAM_INT);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function excluir(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM usuarios WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }
}