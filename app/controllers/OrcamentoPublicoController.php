<?php

require_once __DIR__ . '/../models/Orcamento.php';
require_once __DIR__ . '/../models/RateLimit.php';
require_once __DIR__ . '/../services/EmailService.php';

class OrcamentoPublicoController
{
    private const TIPOS = ['Site institucional', 'Landing Page', 'Sistema Web', 'Aplicativo', 'SaaS', 'E-commerce', 'Outro'];
    private const OBJETIVOS = ['Apresentar minha empresa', 'Gerar leads', 'Aumentar vendas', 'Automatizar processos', 'Criar um produto digital', 'Outro'];
    private const POSSUI = ['Não, do zero', 'Quero reformular o que tenho', 'Quero adicionar funcionalidades'];
    private const FAIXAS = ['Ainda não sei', 'Até R$ 2.000', 'R$ 2.000 – R$ 5.000', 'R$ 5.000 – R$ 10.000', 'R$ 10.000 – R$ 20.000', 'Acima de R$ 20.000'];
    private const INICIOS = ['Assim que possível', 'Em até 1 mês', 'Em até 3 meses', 'Ainda estou planejando'];

    private const MAX_ARQUIVOS = 5;
    private const MAX_BYTES    = 5 * 1024 * 1024;
    private const MAX_TOTAL    = 20 * 1024 * 1024;

    // extensão => tipos reais aceitos (conferidos pelo conteúdo, não pelo nome)
    private const ANEXOS = [
        'pdf'  => ['application/pdf'],
        'png'  => ['image/png'],
        'jpg'  => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'doc'  => ['application/msword', 'application/CDFV2', 'application/x-ole-storage', 'application/octet-stream'],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip', 'application/octet-stream'],
        'ppt'  => ['application/vnd.ms-powerpoint', 'application/CDFV2', 'application/x-ole-storage', 'application/octet-stream'],
        'pptx' => ['application/vnd.openxmlformats-officedocument.presentationml.presentation', 'application/zip', 'application/octet-stream'],
    ];

    public function enviar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(405, ['sucesso' => false, 'mensagem' => 'Método não permitido.']);
        }

        // Corpo maior que post_max_size: o PHP descarta tudo
        if (empty($_POST) && empty($_FILES) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
            $this->erro('Os arquivos são grandes demais. Envie até 5 arquivos de 5 MB cada.', 413);
        }

        // Honeypot: robôs preenchem. Responde "sucesso" sem gravar nada.
        if (trim((string) ($_POST['site'] ?? '')) !== '') {
            $this->json(200, ['sucesso' => true, 'mensagem' => 'Recebemos sua solicitação!']);
        }

        $nome      = $this->texto('nome', 100);
        $empresa   = $this->texto('nome_empresa', 100);
        $email     = mb_strtolower($this->texto('email', 150));
        $telefone  = $this->texto('telefone', 20);
        $descricao = trim((string) ($_POST['descricao_projeto'] ?? ''));

        if ($nome === '')    $this->erro('Informe seu nome.');
        if ($empresa === '') $this->erro('Informe o nome da empresa.');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $this->erro('Informe um e-mail válido.');
        if (!preg_match('/^[0-9()+\-\s]{8,20}$/', $telefone)) $this->erro('Informe um WhatsApp válido.');
        if (mb_strlen($descricao) < 10 || mb_strlen($descricao) > 5000) {
            $this->erro('Conte sua ideia com pelo menos 10 e no máximo 5000 caracteres.');
        }

        $tipos       = $this->escolhas('tipo_projeto', self::TIPOS, 'tipo_projeto_outro', 'o tipo de projeto');
        $objetivos   = $this->escolhas('objetivo_projeto', self::OBJETIVOS, 'objetivo_projeto_outro', 'o objetivo');
        $possui      = $this->unica('possui_atualmente', self::POSSUI);
        $faixa       = $this->unica('faixa_investimento', self::FAIXAS);
        $inicio      = $this->unica('inicio_projeto', self::INICIOS);
        $referencias = $this->texto('referencias_links', 1000);

        $arquivos = $this->lerArquivos();

        // Limite de envios (por IP e por e-mail)
        $rl         = new RateLimit();
        $chaveIp    = RateLimit::chave($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
        $chaveEmail = RateLimit::chave($email);

        if ($rl->excedeu('orcamento', $chaveIp, 5, 60) || $rl->excedeu('orcamento', $chaveEmail, 3, 60)) {
            $this->json(429, ['sucesso' => false, 'mensagem' => 'Você enviou muitas solicitações. Tente novamente mais tarde.']);
        }
        $rl->registrar('orcamento', $chaveIp);
        $rl->registrar('orcamento', $chaveEmail);

        $pasta = __DIR__ . '/../../storage/orcamentos';
        if (!is_dir($pasta) && !mkdir($pasta, 0755, true)) {
            error_log('Não foi possível criar a pasta de anexos.');
            $this->json(500, ['sucesso' => false, 'mensagem' => 'Erro interno. Tente novamente.']);
        }

        try {
            (new Orcamento())->criarComAnexos([
                'nome' => $nome, 'empresa' => $empresa, 'email' => $email, 'telefone' => $telefone,
                'tipos' => $tipos, 'objetivos' => $objetivos, 'descricao' => $descricao,
                'possui' => $possui, 'referencias' => $referencias, 'faixa' => $faixa, 'inicio' => $inicio,
            ], $arquivos, $pasta);
        } catch (Throwable $e) {
            error_log('Erro ao gravar orçamento: ' . $e->getMessage());
            $this->json(500, ['sucesso' => false, 'mensagem' => 'Erro interno. Tente novamente.']);
        }

        // Aviso interno. Se falhar, o pedido já está salvo e o cliente não é afetado.
        try {
            $config = require __DIR__ . '/../../config/mail.php';
            (new EmailService())->avisarNovoOrcamento([
                'nome' => $nome, 'empresa' => $empresa, 'email' => $email, 'telefone' => $telefone,
                'tipos' => $tipos, 'faixa' => $faixa, 'descricao' => $descricao,
            ], $config['base_url'] . '/app/views/admin/orcamentos.php');
        } catch (Throwable $e) {
            error_log('Erro no aviso de orçamento: ' . $e->getMessage());
        }

        $this->json(201, ['sucesso' => true, 'mensagem' => 'Recebemos sua solicitação! Responderemos em até 48 horas.']);
    }

    /* ---------- Campos ---------- */

    private function texto(string $campo, int $max): string
    {
        $v = trim((string) ($_POST[$campo] ?? ''));
        if (mb_strlen($v) > $max) {
            $this->erro("O campo \"{$campo}\" passou do limite de {$max} caracteres.");
        }
        return $v;
    }

    /** Escolha única: vazio é permitido; qualquer valor precisa estar na lista. */
    private function unica(string $campo, array $permitidas): string
    {
        $v = trim((string) ($_POST[$campo] ?? ''));
        if ($v !== '' && !in_array($v, $permitidas, true)) {
            $this->erro('Uma das opções enviadas é inválida. Recarregue a página e tente de novo.');
        }
        return $v;
    }

    /** Múltipla escolha obrigatória. "Outro" vira "Outro: texto". */
    private function escolhas(string $campo, array $permitidas, string $campoOutro, string $rotulo): string
    {
        $itens = array_values(array_unique(array_filter(array_map('trim', explode(',', (string) ($_POST[$campo] ?? ''))))));

        if (!$itens) {
            $this->erro("Selecione {$rotulo}.");
        }
        foreach ($itens as $i) {
            if (!in_array($i, $permitidas, true)) {
                $this->erro('Uma das opções enviadas é inválida. Recarregue a página e tente de novo.');
            }
        }

        $outro = trim(preg_replace('/[\s,]+/u', ' ', (string) ($_POST[$campoOutro] ?? '')));
        $outro = mb_substr($outro, 0, 100);
        $itens = array_map(fn ($i) => ($i === 'Outro' && $outro !== '') ? 'Outro: ' . $outro : $i, $itens);

        return implode(', ', $itens);
    }

    /* ---------- Anexos ---------- */

    private function lerArquivos(): array
    {
        if (empty($_FILES['anexos'])) {
            return [];
        }

        $f     = $_FILES['anexos'];
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $saida = [];
        $total = 0;

        foreach ((array) $f['name'] as $i => $nomeOriginal) {
            $erro = $f['error'][$i];

            if ($erro === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            if ($erro === UPLOAD_ERR_INI_SIZE || $erro === UPLOAD_ERR_FORM_SIZE) {
                $this->erro('Cada anexo pode ter até 5 MB.');
            }
            if ($erro !== UPLOAD_ERR_OK || !is_uploaded_file($f['tmp_name'][$i])) {
                $this->erro('Não foi possível receber um dos anexos. Tente novamente.');
            }
            if (count($saida) >= self::MAX_ARQUIVOS) {
                $this->erro('Anexe no máximo ' . self::MAX_ARQUIVOS . ' arquivos.');
            }

            $tamanho = (int) $f['size'][$i];
            $total  += $tamanho;
            if ($tamanho > self::MAX_BYTES) {
                $this->erro('Cada anexo pode ter até 5 MB.');
            }
            if ($total > self::MAX_TOTAL) {
                $this->erro('O total dos anexos passou de 20 MB.');
            }

            $nome = trim(preg_replace('/[\x00-\x1F\x7F]/u', '', basename(str_replace('\\', '/', (string) $nomeOriginal))));
            $ext  = strtolower(pathinfo($nome, PATHINFO_EXTENSION));
            $mime = (string) $finfo->file($f['tmp_name'][$i]);

            if (!isset(self::ANEXOS[$ext]) || !in_array($mime, self::ANEXOS[$ext], true)) {
                $this->erro('O arquivo "' . mb_substr($nome, 0, 60) . '" não é de um tipo permitido (PDF, DOC, DOCX, PPT, PPTX, PNG ou JPG).');
            }

            $saida[] = [
                'tmp'           => $f['tmp_name'][$i],
                'nome_original' => mb_substr($nome, 0, 255),
                'nome_arquivo'  => bin2hex(random_bytes(20)),
                'mime'          => $mime,
                'tamanho'       => $tamanho,
            ];
        }

        return $saida;
    }

    private function erro(string $mensagem, int $status = 422): never
    {
        $this->json($status, ['sucesso' => false, 'mensagem' => $mensagem]);
    }

    private function json(int $status, array $dados): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($dados, JSON_UNESCAPED_UNICODE);
        exit;
    }
}