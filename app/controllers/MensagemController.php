<?php

require_once __DIR__ . '/../models/Mensagem.php';
require_once __DIR__ . '/../models/RateLimit.php';
require_once __DIR__ . '/../services/EmailService.php';

class MensagemController
{
    private Mensagem $model;

    public function __construct()
    {
        $this->model = new Mensagem();
    }

    public function listar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->json(405, ['sucesso' => false, 'mensagem' => 'Método não permitido.']);
        }

        $mensagens = array_map(fn ($m) => [
            'id'       => (int) $m['id'],
            'nome'     => $m['nome'],
            'empresa'  => $m['empresa'],
            'email'    => $m['email'],
            'telefone' => $m['telefone'],
            'assunto'  => $m['assunto'],
            'mensagem' => $m['mensagem'],
            'lida'     => (bool) $m['lida'],
            'arquivada' => (bool) $m['arquivada'],
            'data'     => str_replace(' ', 'T', $m['criado_em']),
            'respostas'     => (int) $m['respostas'],
            'respondida_em' => $m['respondida_em'] ? str_replace(' ', 'T', $m['respondida_em']) : null,
        ], $this->model->listar());

        $this->json(200, ['sucesso' => true, 'mensagens' => $mensagens]);
    }

    public function marcarLida(): void
    {
        $id = $this->lerId();
        $this->model->marcarComoLida($id);

        $this->json(200, ['sucesso' => true]);
    }

    public function excluir(): void
    {
        $id  = $this->lerId();
        $msg = $this->model->buscarPorId($id);

        if (!$msg['arquivada']) {
            $this->json(409, ['sucesso' => false, 'mensagem' => 'Arquive a mensagem antes de excluir.']);
        }

        $this->model->excluir($id);
        $this->json(200, ['sucesso' => true, 'mensagem' => 'Mensagem excluída.']);
    }

    /** Lê e valida o id do corpo do POST; responde 404 se a mensagem não existir. */
    private function lerId(): int
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(405, ['sucesso' => false, 'mensagem' => 'Método não permitido.']);
        }

        $dados = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $id    = filter_var($dados['id'] ?? null, FILTER_VALIDATE_INT);

        if (!$id || $id < 1) {
            $this->json(422, ['sucesso' => false, 'mensagem' => 'Dados inválidos.']);
        }

        if ($this->model->buscarPorId($id) === null) {
            $this->json(404, ['sucesso' => false, 'mensagem' => 'Mensagem não encontrada.']);
        }

        return $id;
    }

    private function json(int $status, array $dados): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($dados, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    public function responder(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(405, ['sucesso' => false, 'mensagem' => 'Método não permitido.']);
        }

        $dados    = json_decode(file_get_contents('php://input'), true) ?? [];
        $id       = filter_var($dados['id'] ?? null, FILTER_VALIDATE_INT);
        $resposta = trim((string) ($dados['resposta'] ?? ''));

        if (!$id || $id < 1) {
            $this->json(422, ['sucesso' => false, 'mensagem' => 'Dados inválidos.']);
        }
        if ($resposta === '' || mb_strlen($resposta) > 5000) {
            $this->json(422, ['sucesso' => false, 'mensagem' => 'Escreva uma resposta de até 5000 caracteres.']);
        }

        $msg = $this->model->buscarPorId($id);
        if ($msg === null) {
            $this->json(404, ['sucesso' => false, 'mensagem' => 'Mensagem não encontrada.']);
        }

        // Limite por admin: 20 respostas por hora
        $usuarioId = (int) $_SESSION['usuario_id'];
        $rl        = new RateLimit();
        $chave     = RateLimit::chave('uid:' . $usuarioId);

        if ($rl->excedeu('resposta_msg', $chave, 20, 60)) {
            $this->json(429, ['sucesso' => false, 'mensagem' => 'Limite de respostas por hora atingido. Tente mais tarde.']);
        }
        $rl->registrar('resposta_msg', $chave);

        $enviado = (new EmailService())->enviarRespostaMensagem(
            $msg['email'], $msg['nome'], $msg['assunto'], $resposta, $msg['mensagem']
        );

        if (!$enviado) {
            $this->json(502, ['sucesso' => false, 'mensagem' => 'Não foi possível enviar o e-mail. Tente novamente.']);
        }

        try {
            $this->model->registrarResposta($id, $usuarioId, $resposta);
            $this->model->marcarComoLida($id);
        } catch (Throwable $e) {
            // O e-mail já saiu; só registra o problema do histórico
            error_log('Erro ao gravar resposta: ' . $e->getMessage());
        }

        $this->json(200, [
            'sucesso'       => true,
            'mensagem'      => 'Resposta enviada.',
            'respondida_em' => date('Y-m-d\TH:i:s'),
        ]);
    }
    public function arquivar(): void
    {
        $id    = $this->lerId();   // valida o id e responde 404 se não existir
        $dados = json_decode(file_get_contents('php://input'), true) ?? [];
        $arquivar = filter_var($dados['arquivada'] ?? true, FILTER_VALIDATE_BOOLEAN);

        $this->model->definirArquivada($id, $arquivar);

        $this->json(200, [
            'sucesso'  => true,
            'mensagem' => $arquivar ? 'Mensagem arquivada.' : 'Mensagem desarquivada.',
        ]);
    }
}