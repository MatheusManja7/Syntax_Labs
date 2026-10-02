<?php

require_once __DIR__ . '/../models/Usuario.php';

const SESSAO_INATIVIDADE = 1800; // 30 minutos

function iniciarSessao(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }
}

function encerrarSessao(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

/** true se há login, sem inatividade excessiva e com o usuário ainda ativo no banco. */
function sessaoValida(): bool
{
    iniciarSessao();

    if (empty($_SESSION['usuario_id'])) {
        return false;
    }

    $ultima = $_SESSION['ultima_atividade'] ?? time();
    if (time() - $ultima > SESSAO_INATIVIDADE) {
        encerrarSessao();
        return false;
    }

    if ((new Usuario())->buscarPorId((int) $_SESSION['usuario_id']) === null) {
        encerrarSessao();
        return false;
    }

    $_SESSION['ultima_atividade'] = time();
    return true;
}