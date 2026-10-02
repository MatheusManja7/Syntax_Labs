<?php

require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/PasswordReset.php';
require_once __DIR__ . '/../services/EmailService.php';
require_once __DIR__ . '/../models/RateLimit.php';

class AuthController
{
    private Usuario $usuarioModel;

    public function __construct()
    {
        $this->usuarioModel = new Usuario();
    }

    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(405, ['sucesso' => false, 'mensagem' => 'Método não permitido.']);
        }

        // Aceita JSON (fetch) ou form comum
        $dados = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        $email = trim($dados['email'] ?? '');
        $senha = $dados['senha'] ?? '';

        if ($email === '' || $senha === '') {
            $this->json(422, ['sucesso' => false, 'mensagem' => 'Preencha e-mail e senha.']);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->json(422, ['sucesso' => false, 'mensagem' => 'E-mail inválido.']);
        }

        // Limite de tentativas (por e-mail e por IP)
        $rl         = new RateLimit();
        $chaveEmail = RateLimit::chave($email);
        $chaveIp    = RateLimit::chave($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');

        if ($rl->excedeu('login', $chaveEmail, 5, 15) || $rl->excedeu('login', $chaveIp, 20, 15)) {
            $this->json(429, [
                'sucesso'  => false,
                'mensagem' => 'Muitas tentativas. Aguarde 15 minutos e tente novamente.'
            ]);
        }

        $usuario = $this->usuarioModel->autenticar($email, $senha);

        if ($usuario === null) {
            $rl->registrar('login', $chaveEmail);
            $rl->registrar('login', $chaveIp);

            // Mensagem genérica: não revela se o erro foi no e-mail ou na senha
            $this->json(401, ['sucesso' => false, 'mensagem' => 'E-mail ou senha incorretos.']);
        }

        // Evita session fixation
        session_regenerate_id(true);

        // Login certo zera as falhas desse e-mail
        $rl->limpar('login', $chaveEmail);

        $_SESSION['usuario_id']   = $usuario['id'];
        $_SESSION['usuario_nome'] = $usuario['nome'];
        $_SESSION['ultima_atividade'] = time();

        $this->json(200, ['sucesso' => true, 'mensagem' => 'Login realizado com sucesso.']);
    }

    private function json(int $status, array $dados): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($dados, JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function solicitarRecuperacao(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(405, ['sucesso' => false, 'mensagem' => 'Método não permitido.']);
        }

        $dados = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $email = trim($dados['email'] ?? '');

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->json(422, ['sucesso' => false, 'mensagem' => 'Informe um e-mail válido.']);
        }

        // Limite de pedidos de link (por e-mail e por IP)
        $rl         = new RateLimit();
        $chaveEmail = RateLimit::chave($email);
        $chaveIp    = RateLimit::chave($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');

        if ($rl->excedeu('recuperacao', $chaveEmail, 3, 60) || $rl->excedeu('recuperacao', $chaveIp, 10, 60)) {
            $this->json(429, [
                'sucesso'  => false,
                'mensagem' => 'Muitos pedidos. Aguarde um pouco e tente novamente.'
            ]);
        }

        // Conta TODO pedido, exista o e-mail ou não
        $rl->registrar('recuperacao', $chaveEmail);
        $rl->registrar('recuperacao', $chaveIp);

        $usuario = $this->usuarioModel->buscarPorEmail($email);

        if ($usuario !== null) {
            $token = (new PasswordReset())->criar((int) $usuario['id']);

            $config = require __DIR__ . '/../../config/mail.php';
            $link   = $config['base_url'] . '/app/views/auth/nova_senha.html?token=' . $token;

            // Se falhar, só registra no log: a resposta ao usuário continua neutra
            (new EmailService())->enviarRecuperacaoSenha($usuario['email'], $usuario['nome'], $link);
        }

        // Resposta IGUAL existindo ou não o e-mail (não revela quem está cadastrado)
        $this->json(200, [
            'sucesso'  => true,
            'mensagem' => 'Se o e-mail estiver cadastrado, você receberá um link para redefinir sua senha.'
        ]);
    }

    public function validarToken(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(405, ['sucesso' => false, 'mensagem' => 'Método não permitido.']);
        }

        $dados = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $token = $this->lerToken($dados);

        if ($token === null || (new PasswordReset())->validar($token) === null) {
            $this->json(400, ['sucesso' => false, 'mensagem' => 'Link inválido ou expirado.']);
        }

        $this->json(200, ['sucesso' => true]);
    }

    public function redefinirSenha(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(405, ['sucesso' => false, 'mensagem' => 'Método não permitido.']);
        }

        $dados       = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $token       = $this->lerToken($dados);
        $senha       = $dados['senha'] ?? '';
        $confirmacao = $dados['confirmacao'] ?? '';

        if ($token === null) {
            $this->json(400, ['sucesso' => false, 'mensagem' => 'Link inválido ou expirado.']);
        }

        if ($senha !== $confirmacao) {
            $this->json(422, ['sucesso' => false, 'mensagem' => 'As senhas não coincidem.']);
        }

        if (strlen($senha) < 8
            || !preg_match('/[A-Z]/', $senha)
            || !preg_match('/[0-9]/', $senha)
            || strlen($senha) > 72) {
            $this->json(422, [
                'sucesso'  => false,
                'mensagem' => 'A senha deve ter no mínimo 8 caracteres, 1 letra maiúscula e 1 número.'
            ]);
        }

        $reset     = new PasswordReset();
        $usuarioId = $reset->validar($token);

        if ($usuarioId === null) {
            $this->json(400, ['sucesso' => false, 'mensagem' => 'Link inválido ou expirado.']);
        }

        $pdo = Database::getConnection();

        try {
            $pdo->beginTransaction();
            $this->usuarioModel->atualizarSenha($usuarioId, $senha);
            $reset->marcarComoUsado($token);
            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('Erro ao redefinir senha: ' . $e->getMessage());
            $this->json(500, ['sucesso' => false, 'mensagem' => 'Erro interno. Tente novamente.']);
        }

        $this->json(200, ['sucesso' => true, 'mensagem' => 'Senha atualizada com sucesso.']);
    }

    private function lerToken(array $dados): ?string
    {
        $token = trim($dados['token'] ?? '');

        return preg_match('/^[a-f0-9]{64}$/', $token) ? $token : null;
    }

    public function logout(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(405, ['sucesso' => false, 'mensagem' => 'Método não permitido.']);
        }

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }

        session_destroy();

        $this->json(200, ['sucesso' => true]);
    }
}