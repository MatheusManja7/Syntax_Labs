<?php

require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/RateLimit.php';

class PerfilController
{
    private Usuario $usuarioModel;

    public function __construct()
    {
        $this->usuarioModel = new Usuario();
    }

    public function atualizarNome(): void
    {
        $dados = $this->lerPost();
        $nome  = trim($dados['nome'] ?? '');

        if ($nome === '' || mb_strlen($nome) > 100) {
            $this->json(422, ['sucesso' => false, 'mensagem' => 'Informe um nome de até 100 caracteres.']);
        }

        $id = (int) $_SESSION['usuario_id'];
        $this->usuarioModel->atualizarNome($id, $nome);

        // A saudação da home usa o nome da sessão
        $_SESSION['usuario_nome'] = $nome;

        $this->json(200, ['sucesso' => true, 'mensagem' => 'Perfil atualizado.', 'nome' => $nome]);
    }

    public function alterarSenha(): void
    {
        $dados       = $this->lerPost();
        $atual       = $dados['senha_atual'] ?? '';
        $nova        = $dados['senha_nova'] ?? '';
        $confirmacao = $dados['senha_confirma'] ?? '';

        if ($atual === '' || $nova === '' || $confirmacao === '') {
            $this->json(422, ['sucesso' => false, 'mensagem' => 'Preencha todos os campos.']);
        }

        if ($nova !== $confirmacao) {
            $this->json(422, ['sucesso' => false, 'mensagem' => 'As senhas não coincidem.']);
        }

        if (strlen($nova) < 8
            || strlen($nova) > 72
            || !preg_match('/[A-Z]/', $nova)
            || !preg_match('/[0-9]/', $nova)) {
            $this->json(422, [
                'sucesso'  => false,
                'mensagem' => 'A senha deve ter de 8 a 72 caracteres, com 1 letra maiúscula e 1 número.'
            ]);
        }

        $id = (int) $_SESSION['usuario_id'];

        // Limite de tentativas da senha atual (uma sessão roubada não vira força bruta)
        $rl    = new RateLimit();
        $chave = RateLimit::chave('uid:' . $id);

        if ($rl->excedeu('senha_atual', $chave, 5, 15)) {
            $this->json(429, [
                'sucesso'  => false,
                'mensagem' => 'Muitas tentativas. Aguarde 15 minutos e tente novamente.'
            ]);
        }

        if (!$this->usuarioModel->senhaConfere($id, $atual)) {
            $rl->registrar('senha_atual', $chave);
            $this->json(403, ['sucesso' => false, 'mensagem' => 'A senha atual está incorreta.']);
        }

        if ($atual === $nova) {
            $this->json(422, ['sucesso' => false, 'mensagem' => 'A nova senha deve ser diferente da atual.']);
        }

        $this->usuarioModel->atualizarSenha($id, $nova);
        $rl->limpar('senha_atual', $chave);

        // Novo ID de sessão após mudança de credencial
        session_regenerate_id(true);

        $this->json(200, ['sucesso' => true, 'mensagem' => 'Senha alterada com sucesso.']);
    }

    private function lerPost(): array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(405, ['sucesso' => false, 'mensagem' => 'Método não permitido.']);
        }

        return json_decode(file_get_contents('php://input'), true) ?? $_POST;
    }

    private function json(int $status, array $dados): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($dados, JSON_UNESCAPED_UNICODE);
        exit;
    }
}