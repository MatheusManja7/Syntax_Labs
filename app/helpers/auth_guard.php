<?php

require_once __DIR__ . '/sessao.php';

if (!sessaoValida()) {
    header('Location: ../auth/login.html');
    exit;
}

header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');