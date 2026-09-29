<?php

require_once __DIR__ . '/../../config/database.php';

class RateLimit
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public static function chave(string $valor): string
    {
        return hash('sha256', mb_strtolower(trim($valor)));
    }

    /** Já bateu o limite de tentativas dentro da janela? */
    public function excedeu(string $tipo, string $chave, int $max, int $janelaMin): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM tentativas
              WHERE tipo = :tipo AND chave = :chave
                AND criado_em > DATE_SUB(NOW(), INTERVAL :min MINUTE)'
        );
        $stmt->bindValue(':tipo', $tipo);
        $stmt->bindValue(':chave', $chave);
        $stmt->bindValue(':min', $janelaMin, PDO::PARAM_INT);
        $stmt->execute();

        return (int) $stmt->fetchColumn() >= $max;
    }

    public function registrar(string $tipo, string $chave): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO tentativas (tipo, chave) VALUES (:tipo, :chave)'
        );
        $stmt->execute([':tipo' => $tipo, ':chave' => $chave]);

        // Limpeza oportunista: apaga registros com mais de 1 dia
        $this->pdo->exec('DELETE FROM tentativas WHERE criado_em < DATE_SUB(NOW(), INTERVAL 1 DAY)');
    }

    public function limpar(string $tipo, string $chave): void
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM tentativas WHERE tipo = :tipo AND chave = :chave'
        );
        $stmt->execute([':tipo' => $tipo, ':chave' => $chave]);
    }
}