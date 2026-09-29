<?php

// Só permite execução pelo terminal (CLI), nunca pelo navegador
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Acesso negado.');
}

require_once __DIR__ . '/../config/database.php';

$nome  = 'Administrador';
$email = 'matheusmanja7@gmail.com';
$senha = 'Matheus@77';

$pdo = Database::getConnection();

// Evita duplicar se o script for executado duas vezes
$stmt = $pdo->prepare('SELECT id FROM usuarios WHERE email = :email');
$stmt->execute([':email' => $email]);

if ($stmt->fetch()) {
    exit("Usuário {$email} já existe.\n");
}

$hash = password_hash($senha, PASSWORD_DEFAULT);

$stmt = $pdo->prepare(
    'INSERT INTO usuarios (nome, email, senha_hash) VALUES (:nome, :email, :hash)'
);
$stmt->execute([
    ':nome'  => $nome,
    ':email' => $email,
    ':hash'  => $hash,
]);

echo "Admin criado com sucesso: {$email}\n";