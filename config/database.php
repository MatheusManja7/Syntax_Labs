<?php

class Database
{
    private static ?PDO $instance = null;

    private const HOST     = 'localhost';
    private const DB_NAME  = 'syntax_labs';
    private const USER     = 'root';
    private const PASSWORD = '';   // padrão do XAMPP é vazio
    private const CHARSET  = 'utf8mb4';

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $dsn = 'mysql:host=' . self::HOST
                 . ';dbname=' . self::DB_NAME
                 . ';charset=' . self::CHARSET;

            try {
                self::$instance = new PDO($dsn, self::USER, self::PASSWORD, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                // Não exponha detalhes da conexão para o usuário
                error_log('Erro de conexão: ' . $e->getMessage());
                http_response_code(500);
                exit('Erro interno no servidor.');
            }
        }

        return self::$instance;
    }
}