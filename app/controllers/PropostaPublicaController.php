<?php

require_once __DIR__ . '/../models/Orcamento.php';
require_once __DIR__ . '/../models/RateLimit.php';
require_once __DIR__ . '/../services/EmailService.php';

class PropostaPublicaController
{
    private Orcamento $model;

    public function __construct()
    {
        $this->model = new Orcamento();
    }

    public function ver(): void
    {
        $o = $this->carregar('proposta_ver', 60);

        $this->json(200, [
            'sucesso'  => true,
            'proposta' => [
                'estado'    => $this->estado($o),
                'cliente'   => $o['nome'],
                'empresa'   => $o['empresa'],
                'titulo'    => $o['prop_titulo'],
                'escopo'    => $o['prop_escopo'],
                'prazo'     => $o['prop_prazo'],
                'valor_min' => (float) $o['prop_valor_min'],
                'valor_max' => (float) $o['prop_valor_max'],
                'pagamento' => $o['prop_pagamento'],
                'validade'  => $o['prop_validade'],
                'obs'       => $o['prop_obs'],
            ],
        ]);
    }

    public function responder(): void
    {
        $o = $this->carregar('proposta_resp', 10, $dados);

        $decisao = $dados['decisao'] ?? '';
        if (!in_array($decisao, ['aceitar', 'rejeitar'], true)) {
            $this->json(422, ['sucesso' => false, 'mensagem' => 'Dados inválidos.']);
        }

        $estado = $this->estado($o);
        if ($estado === 'expirada') {
            $this->json(410, ['sucesso' => false, 'mensagem' => 'Esta proposta expirou.', 'estado' => $estado]);
        }
        if ($estado !== 'aberta') {
            $this->json(409, ['sucesso' => false, 'mensagem' => 'Esta proposta já foi respondida.', 'estado' => $estado]);
        }

        $novo = $decisao === 'aceitar' ? 'aceito' : 'rejeitado';

        if (!$this->model->registrarDecisao((int) $o['id'], $novo)) {
            $this->json(409, ['sucesso' => false, 'mensagem' => 'Esta proposta já foi respondida.']);
        }

        // Aviso interno. Se falhar, a decisão já está salva.
        try {
            $config = require __DIR__ . '/../../config/mail.php';
            (new EmailService())->avisarDecisaoOrcamento($o, $novo, $config['base_url'] . '/app/views/admin/orcamentos.php');
        } catch (Throwable $e) {
            error_log('Erro no aviso de decisão: ' . $e->getMessage());
        }

        $this->json(200, ['sucesso' => true, 'estado' => $novo]);
    }

    /* ---------- Auxiliares ---------- */

    /** Valida método, limite, token e devolve o orçamento. Todo acesso conta no limite. */
    private function carregar(string $tipo, int $max, ?array &$dadosSaida = null): array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(405, ['sucesso' => false, 'mensagem' => 'Método não permitido.']);
        }

        $dadosSaida = json_decode(file_get_contents('php://input'), true) ?? [];

        $rl    = new RateLimit();
        $chave = RateLimit::chave($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');

        if ($rl->excedeu($tipo, $chave, $max, 60)) {
            $this->json(429, ['sucesso' => false, 'mensagem' => 'Muitas tentativas. Tente novamente mais tarde.']);
        }
        $rl->registrar($tipo, $chave);

        $token = trim((string) ($dadosSaida['token'] ?? ''));
        $o = preg_match('/^[a-f0-9]{64}$/', $token)
            ? $this->model->buscarPorTokenHash(hash('sha256', $token))
            : null;

        if ($o === null || !in_array($o['status'], ['enviado', 'aceito', 'rejeitado'], true)) {
            $this->json(404, ['sucesso' => false, 'mensagem' => 'Link inválido ou substituído por uma proposta mais recente.']);
        }

        return $o;
    }

    private function estado(array $o): string
    {
        if ($o['status'] === 'aceito' || $o['status'] === 'rejeitado') {
            return $o['status'];
        }

        // Vale até o fim do dia da validade
        return ($o['prop_validade'] !== null && $o['prop_validade'] < date('Y-m-d')) ? 'expirada' : 'aberta';
    }

    private function json(int $status, array $dados): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        echo json_encode($dados, JSON_UNESCAPED_UNICODE);
        exit;
    }
}