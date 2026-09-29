<?php

session_set_cookie_params([
    'httponly' => true,     // JS não consegue ler o cookie da sessão
    'samesite' => 'Lax',    // proteção básica contra CSRF
]);
session_start();

require_once __DIR__ . '/../../app/controllers/AuthController.php';

(new AuthController())->login();