<?php

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

if (empty($_SESSION['usuario_id'])) {
    header('Location: ../auth/login.html');
    exit;
}

// Impede o navegador de mostrar a página do cache depois do logout (botão "voltar")
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');