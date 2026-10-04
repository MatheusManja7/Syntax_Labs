<?php

require_once __DIR__ . '/../models/Mensagem.php';
require_once __DIR__ . '/../models/RateLimit.php';

class ContatoController
{
    public function enviar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(405, ['sucesso' => false, 'mensagem' => 'Método não permitido.']);
        }

        $dados = json_decode(file_get_contents('php://input'), true);
        if (!is_array($dados)) {
            $this->json(422, ['sucesso' => false, 'mensagem' => 'Dados inválidos.']);
        }

        // Honeypot: robôs preenchem. Responde "sucesso" sem gravar nada.
        if (trim((string) ($dados['site'] ?? '')) !== '') {
            $this->json(200, ['sucesso' => true, 'mensagem' => 'Mensagem enviada com sucesso!']);
        }

        $nome     = trim((string) ($dados['nome'] ?? ''));
        $empresa  = trim((string) ($dados['empresa'] ?? ''));
        $email    = mb_strtolower(trim((string) ($dados['email'] ?? '')));
        $telefone = trim((string) ($dados['telefone'] ?? ''));
        $assunto  = trim((string) ($dados['assunto'] ?? ''));
        $mensagem = trim((string) ($dados['mensagem'] ?? ''));

        if ($nome === '' || mb_strlen($nome) > 100) {
            $this->erro('Informe seu nome (até 100 caracteres).');
        }
        if (mb_strlen($empresa) > 100) {
            $this->erro('O nome da empresa pode ter até 100 caracteres.');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 150) {
            $this->erro('Informe um e-mail válido.');
        }
        if ($telefone !== '' && !preg_match('/^[0-9()+\-\s]{8,20}$/', $telefone)) {
            $this->erro('Informe um telefone válido.');
        }
        if ($assunto === '' || mb_strlen($assunto) > 150) {
            $this->erro('Informe o assunto (até 150 caracteres).');
        }
        if (mb_strlen($mensagem) < 10 || mb_strlen($mensagem) > 5000) {
            $this->erro('A mensagem deve ter entre 10 e 5000 caracteres.');
        }

        // Limite de envios (por IP e por e-mail). Todo envio válido conta.
        $rl         = new RateLimit();
        $chaveIp    = RateLimit::chave($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
        $chaveEmail = RateLimit::chave($email);

        if ($rl->excedeu('contato', $chaveIp, 5, 60) || $rl->excedeu('contato', $chaveEmail, 3, 60)) {
            $this->json(429, [
                'sucesso'  => false,
                'mensagem' => 'Você enviou muitas mensagens. Tente novamente mais tarde.'
            ]);
        }

        $rl->registrar('contato', $chaveIp);
        $rl->registrar('contato', $chaveEmail);

        try {
            (new Mensagem())->criar(
                $nome,
                $email,
                $empresa !== '' ? $empresa : null,
                $telefone !== '' ? $telefone : null,
                $assunto,
                $mensagem
            );
        } catch (Throwable $e) {
            error_log('Erro ao gravar mensagem: ' . $e->getMessage());
            $this->json(500, ['sucesso' => false, 'mensagem' => 'Erro interno. Tente novamente.']);
        }

        $this->json(201, ['sucesso' => true, 'mensagem' => 'Mensagem enviada com sucesso! Responderemos em breve.']);
    }

    private function erro(string $mensagem): never
    {
        $this->json(422, ['sucesso' => false, 'mensagem' => $mensagem]);
    }

    private function json(int $status, array $dados): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($dados, JSON_UNESCAPED_UNICODE);
        exit;
    }
}