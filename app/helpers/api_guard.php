<?php

require_once __DIR__ . '/sessao.php';

if (!sessaoValida()) {
    http_response_code(401);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(
        ['sucesso' => false, 'mensagem' => 'Sessão expirada. Faça login novamente.'],
        JSON_UNESCAPED_UNICODE
    );
    exit;
}