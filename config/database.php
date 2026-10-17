<?php

class Database
{
    private static ?PDO $instance = null;

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $arquivo = __DIR__ . '/database.local.php';

            if (!is_file($arquivo)) {
                error_log('Arquivo config/database.local.php não encontrado.');
                http_response_code(500);
                exit('Erro interno no servidor.');
            }

            $c = require $arquivo;

            $dsn = 'mysql:host=' . $c['host']
                 . ';dbname=' . $c['db']
                 . ';charset=' . ($c['charset'] ?? 'utf8mb4');

            try {
                self::$instance = new PDO($dsn, $c['user'], $c['pass'], [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                error_log('Erro de conexão: ' . $e->getMessage());
                http_response_code(500);
                exit('Erro interno no servidor.');
            }
        }

        return self::$instance;
    }
}