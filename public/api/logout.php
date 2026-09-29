<?php

session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

require_once __DIR__ . '/../../app/controllers/AuthController.php';

(new AuthController())->logout();   