<?php

require_once __DIR__ . '/../models/Usuario.php';

class AdminController
{
    private Usuario $usuarioModel;

    public function __construct()
    {
        $this->usuarioModel = new Usuario();
    }

    public function cadastrar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(405, ['sucesso' => false, 'mensagem' => 'Método não permitido.']);
        }

        $dados       = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $nome        = trim($dados['nome'] ?? '');
        $email       = mb_strtolower(trim($dados['email'] ?? ''));
        $senha       = $dados['senha'] ?? '';
        $confirmacao = $dados['confirmacao'] ?? '';

        if ($nome === '' || mb_strlen($nome) > 100) {
            $this->json(422, ['sucesso' => false, 'mensagem' => 'Informe um nome de até 100 caracteres.']);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 150) {
            $this->json(422, ['sucesso' => false, 'mensagem' => 'Informe um e-mail válido.']);
        }

        if ($senha !== $confirmacao) {
            $this->json(422, ['sucesso' => false, 'mensagem' => 'As senhas não coincidem.']);
        }

        if (strlen($senha) < 8
            || strlen($senha) > 72   // bcrypt ignora tudo depois de 72 bytes
            || !preg_match('/[A-Z]/', $senha)
            || !preg_match('/[0-9]/', $senha)) {
            $this->json(422, [
                'sucesso'  => false,
                'mensagem' => 'A senha deve ter de 8 a 72 caracteres, com 1 letra maiúscula e 1 número.'
            ]);
        }

        try {
            $this->usuarioModel->criar($nome, $email, $senha);
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $this->json(409, ['sucesso' => false, 'mensagem' => 'Já existe um administrador com esse e-mail.']);
            }
            error_log('Erro ao cadastrar admin: ' . $e->getMessage());
            $this->json(500, ['sucesso' => false, 'mensagem' => 'Erro interno. Tente novamente.']);
        }

        $this->json(201, ['sucesso' => true, 'mensagem' => 'Administrador cadastrado com sucesso.']);
    }

    private function json(int $status, array $dados): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($dados, JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function listar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->json(405, ['sucesso' => false, 'mensagem' => 'Método não permitido.']);
        }

        $admins = array_map(fn ($u) => [
            'id'        => (int) $u['id'],
            'nome'      => $u['nome'],
            'email'     => $u['email'],
            'ativo'     => (bool) $u['ativo'],
            'criado_em' => $u['criado_em'],
            'voce'      => (int) $u['id'] === (int) $_SESSION['usuario_id'],
        ], $this->usuarioModel->listar());

        $this->json(200, ['sucesso' => true, 'admins' => $admins]);
    }

    public function alterarStatus(): void
    {
        $dados = $this->lerPost();
        $id    = filter_var($dados['id'] ?? null, FILTER_VALIDATE_INT);

        if (!$id || $id < 1 || !isset($dados['ativo'])) {
            $this->json(422, ['sucesso' => false, 'mensagem' => 'Dados inválidos.']);
        }

        if ($id === (int) $_SESSION['usuario_id']) {
            $this->json(403, ['sucesso' => false, 'mensagem' => 'Você não pode desativar a sua própria conta.']);
        }

        if (!$this->usuarioModel->existe($id)) {
            $this->json(404, ['sucesso' => false, 'mensagem' => 'Administrador não encontrado.']);
        }

        $this->usuarioModel->alterarStatus($id, (bool) $dados['ativo']);

        $this->json(200, ['sucesso' => true, 'mensagem' => 'Status atualizado.']);
    }

    public function excluir(): void
    {
        $dados = $this->lerPost();
        $id    = filter_var($dados['id'] ?? null, FILTER_VALIDATE_INT);

        if (!$id || $id < 1) {
            $this->json(422, ['sucesso' => false, 'mensagem' => 'Dados inválidos.']);
        }

        if ($id === (int) $_SESSION['usuario_id']) {
            $this->json(403, ['sucesso' => false, 'mensagem' => 'Você não pode excluir a sua própria conta.']);
        }

        if (!$this->usuarioModel->existe($id)) {
            $this->json(404, ['sucesso' => false, 'mensagem' => 'Administrador não encontrado.']);
        }

        $this->usuarioModel->excluir($id);

        $this->json(200, ['sucesso' => true, 'mensagem' => 'Administrador excluído.']);
    }

    private function lerPost(): array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(405, ['sucesso' => false, 'mensagem' => 'Método não permitido.']);
        }

        return json_decode(file_get_contents('php://input'), true) ?? $_POST;
    }
}