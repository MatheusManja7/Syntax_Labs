<?php

require_once __DIR__ . '/../models/Orcamento.php';

class OrcamentoController
{
    private Orcamento $model;

    public function __construct()
    {
        $this->model = new Orcamento();
    }

    public function listar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->json(405, ['sucesso' => false, 'mensagem' => 'Método não permitido.']);
        }

        $lista = array_map(function ($o) {
            $temProposta = $o['prop_titulo'] !== null || $o['prop_escopo'] !== null;

            return [
                'id'         => (int) $o['id'],
                'status'     => $o['status'],
                'nome'       => $o['nome'],
                'empresa'    => $o['empresa'],
                'email'      => $o['email'],
                'telefone'   => $o['telefone'],
                'tipos'      => $this->lista($o['tipos']),
                'objetivos'  => $this->lista($o['objetivos']),
                'descricao'  => $o['descricao'],
                'possui'     => $o['possui'],
                'referencias'=> $o['referencias'],
                'faixa'      => $o['faixa'],
                'inicio'     => $o['inicio'],
                'anexos'     => $o['anexos'],
                'data'       => str_replace(' ', 'T', $o['criado_em']),
                'enviada_em' => $o['enviada_em'] ? str_replace(' ', 'T', $o['enviada_em']) : null,
                'proposta'   => $temProposta ? [
                    'titulo'    => $o['prop_titulo'],
                    'escopo'    => $o['prop_escopo'],
                    'prazo'     => $o['prop_prazo'],
                    'valor_min' => $o['prop_valor_min'] !== null ? (float) $o['prop_valor_min'] : null,
                    'valor_max' => $o['prop_valor_max'] !== null ? (float) $o['prop_valor_max'] : null,
                    'pagamento' => $o['prop_pagamento'],
                    'validade'  => $o['prop_validade'],
                    'obs'       => $o['prop_obs'],
                ] : null,
            ];
        }, $this->model->listar());

        $this->json(200, ['sucesso' => true, 'orcamentos' => $lista]);
    }

    public function marcarEditando(): void
    {
        $id = $this->lerId();
        $this->model->marcarEditando($id, (int) $_SESSION['usuario_id']);

        $this->json(200, ['sucesso' => true]);
    }

    public function salvarRascunho(): void
    {
        $dados = $this->lerCorpo();
        $id    = $this->validarId($dados);
        $o     = $this->model->buscarPorId($id);

        if ($o === null) {
            $this->json(404, ['sucesso' => false, 'mensagem' => 'Orçamento não encontrado.']);
        }
        if (!in_array($o['status'], ['novo', 'editando', 'enviado'], true)) {
            $this->json(409, ['sucesso' => false, 'mensagem' => 'Reabra o orçamento antes de editar.']);
        }

        $p = $this->lerProposta($dados['proposta'] ?? []);
        $this->model->salvarProposta($id, $p, (int) $_SESSION['usuario_id']);

        $this->json(200, ['sucesso' => true, 'mensagem' => 'Rascunho salvo.']);
    }

    public function reabrir(): void
    {
        $id = $this->lerId();
        $o  = $this->model->buscarPorId($id);

        if (!in_array($o['status'], ['aceito', 'rejeitado'], true)) {
            $this->json(409, ['sucesso' => false, 'mensagem' => 'Só orçamentos aceitos ou rejeitados podem ser reabertos.']);
        }

        $this->model->reabrir($id, (int) $_SESSION['usuario_id']);
        $this->json(200, ['sucesso' => true, 'mensagem' => 'Orçamento reaberto para edição.']);
    }

    public function excluir(): void
    {
        $id = $this->lerId();
        $o  = $this->model->buscarPorId($id);

        if (!in_array($o['status'], ['aceito', 'rejeitado'], true)) {
            $this->json(409, ['sucesso' => false, 'mensagem' => 'Só orçamentos finalizados podem ser excluídos.']);
        }

        $this->model->excluir($id);
        $this->json(200, ['sucesso' => true, 'mensagem' => 'Orçamento excluído.']);
    }

    /* ---------- Auxiliares ---------- */

    private function lista(?string $texto): array
    {
        return $texto === null || $texto === ''
            ? []
            : array_values(array_filter(array_map('trim', explode(',', $texto))));
    }

    /** Limpa e limita os campos da proposta. Validação rígida fica no envio. */
    private function lerProposta(array $p): array
    {
        $num = function ($v) {
            if ($v === null || $v === '') {
                return null;
            }
            return is_numeric($v) && $v >= 0 && $v < 100000000 ? round((float) $v, 2) : null;
        };

        $validade = (string) ($p['validade'] ?? '');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $validade) || !checkdate((int) substr($validade, 5, 2), (int) substr($validade, 8, 2), (int) substr($validade, 0, 4))) {
            $validade = '';
        }

        return [
            'titulo'    => mb_substr(trim((string) ($p['titulo'] ?? '')), 0, 150),
            'escopo'    => mb_substr(trim((string) ($p['escopo'] ?? '')), 0, 5000),
            'prazo'     => mb_substr(trim((string) ($p['prazo'] ?? '')), 0, 100),
            'valor_min' => $num($p['valor_min'] ?? null),
            'valor_max' => $num($p['valor_max'] ?? null),
            'pagamento' => mb_substr(trim((string) ($p['pagamento'] ?? '')), 0, 1000),
            'validade'  => $validade,
            'obs'       => mb_substr(trim((string) ($p['obs'] ?? '')), 0, 2000),
        ];
    }

    private function lerCorpo(): array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(405, ['sucesso' => false, 'mensagem' => 'Método não permitido.']);
        }

        return json_decode(file_get_contents('php://input'), true) ?? [];
    }

    private function validarId(array $dados): int
    {
        $id = filter_var($dados['id'] ?? null, FILTER_VALIDATE_INT);
        if (!$id || $id < 1) {
            $this->json(422, ['sucesso' => false, 'mensagem' => 'Dados inválidos.']);
        }

        return $id;
    }

    /** Lê o id do POST e responde 404 se o orçamento não existir. */
    private function lerId(): int
    {
        $id = $this->validarId($this->lerCorpo());

        if ($this->model->buscarPorId($id) === null) {
            $this->json(404, ['sucesso' => false, 'mensagem' => 'Orçamento não encontrado.']);
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
}