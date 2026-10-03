<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailService
{
    private array $config;

    public function __construct()
    {
        $this->config = require __DIR__ . '/../../config/mail.php';
    }

    public function enviarRecuperacaoSenha(string $destinatario, string $nome, string $link): bool
    {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = $this->config['host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $this->config['username'];
            $mail->Password   = $this->config['password'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // porta 465
            $mail->Port       = $this->config['port'];
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom($this->config['from_email'], $this->config['from_name']);
            $mail->addAddress($destinatario, $nome);

            // Logo embutida no e-mail (não depende de URL pública)
            $logo = __DIR__ . '/../../public/assets/logo_name_sf.png';
            $temLogo = is_file($logo);
            if ($temLogo) {
                $mail->addEmbeddedImage($logo, 'logo_syntax', 'logo.png');
            }

            $mail->isHTML(true);
            $mail->Subject = 'Recuperação de senha - Syntax Labs';
            $mail->Body    = $this->templateRecuperacao($nome, $link, $temLogo);
            $mail->AltBody = "Olá, {$nome}!\n\n"
                . "Recebemos um pedido para redefinir a sua senha.\n"
                . "Acesse o link abaixo (vale por 30 minutos e só pode ser usado uma vez):\n\n"
                . "{$link}\n\n"
                . "Se você não pediu isso, ignore este e-mail: sua senha continua a mesma.\n\n"
                . "Syntax Labs";

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log('Erro ao enviar e-mail: ' . $mail->ErrorInfo);
            return false;
        }
    }

    private function templateRecuperacao(string $nome, string $link, bool $temLogo): string
    {
        // Fundo da página (escuro, para a logo branca aparecer)
        $fundo        = '#0a0a0a';
        $fundoTexto   = '#ffffff';   // texto sobre o fundo escuro
        $fundoSuave   = '#a8a8a8';   // texto secundário sobre o fundo escuro

        // Card (branco)
        $card         = '#ffffff';
        $borda        = '#e3ebd8';
        $texto        = '#1a1a1a';   // texto principal no card
        $textoSuave   = '#4a4a4a';   // texto secundário no card

        // Destaques
        $verde        = '#89f200';
        $verdeEscuro  = '#2f7000';   // verde legível sobre branco
        $textoBtn     = '#0d1a00';

        // Caixa de aviso (verde bem claro, texto escuro)
        $avisoFundo   = '#f1fbe0';

        $fTitulo = "'Unbounded', 'Arial Black', Arial, sans-serif";
        $fCorpo  = "'Plus Jakarta Sans', Arial, Helvetica, sans-serif";
        $fCodigo = "'JetBrains Mono', 'Courier New', monospace";

        $nomeSeguro = htmlspecialchars($nome, ENT_QUOTES, 'UTF-8');
        $linkSeguro = htmlspecialchars($link, ENT_QUOTES, 'UTF-8');
        $ano        = date('Y');

        $cabecalho = $temLogo
            ? "<img src='cid:logo_syntax' alt='Syntax Labs' width='190' style='display:block;margin:0 auto;border:0;height:auto;max-width:190px;'>"
            : "<span style='font-family:{$fTitulo};font-size:20px;font-weight:600;color:{$verde};'>Syntax Labs</span>";

        return "<!DOCTYPE html>
<html lang='pt-br'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <meta name='color-scheme' content='light'>
    <meta name='supported-color-schemes' content='light'>
    <title>Recuperação de senha</title>
</head>
<body style='margin:0;padding:0;background-color:{$fundo};'>

    <div style='display:none;max-height:0;overflow:hidden;opacity:0;color:{$fundo};'>
        Use o link para criar uma nova senha. Ele vale por 30 minutos.
    </div>

    <table role='presentation' width='100%' cellpadding='0' cellspacing='0' border='0' bgcolor='{$fundo}' style='background-color:{$fundo};'>
        <tr>
            <td align='center' style='padding:40px 12px;'>

                <table role='presentation' width='560' cellpadding='0' cellspacing='0' border='0' style='width:100%;max-width:560px;'>

                    <!-- Logo -->
                    <tr>
                        <td align='center' style='padding:0 0 28px 0;'>
                            {$cabecalho}
                        </td>
                    </tr>

                    <!-- Card -->
                    <tr>
                        <td bgcolor='{$card}' style='background-color:{$card};border-top:4px solid {$verde};border-radius:12px;padding:40px 36px;'>

                            <p style='margin:0 0 14px 0;font-family:{$fCodigo};font-size:12px;letter-spacing:1px;color:{$verdeEscuro};'>
                                // recuperação de senha
                            </p>

                            <h1 style='margin:0 0 18px 0;font-family:{$fTitulo};font-size:24px;line-height:1.3;font-weight:600;color:{$texto};'>
                                Olá, {$nomeSeguro}!
                            </h1>

                            <p style='margin:0 0 14px 0;font-family:{$fCorpo};font-size:15px;line-height:1.7;color:{$texto};'>
                                Recebemos um pedido para redefinir a senha da sua conta no painel administrativo.
                            </p>

                            <p style='margin:0 0 30px 0;font-family:{$fCorpo};font-size:15px;line-height:1.7;color:{$textoSuave};'>
                                Clique no botão abaixo para criar uma nova senha.
                            </p>

                            <!-- Botão -->
                            <table role='presentation' cellpadding='0' cellspacing='0' border='0' align='center' style='margin:0 auto 30px auto;'>
                                <tr>
                                    <td align='center' bgcolor='{$verde}' style='background-color:{$verde};border-radius:12px;padding:22px 64px;'>
                                        <a href='{$linkSeguro}' target='_blank'
                                           style='display:block;font-family:{$fCorpo};font-size:19px;line-height:24px;font-weight:700;letter-spacing:0.5px;color:#000000;text-decoration:none;white-space:nowrap;'>
                                            <span style='color:#000000;text-decoration:none;'>Redefinir senha</span>
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <!-- Aviso de validade -->
                            <table role='presentation' width='100%' cellpadding='0' cellspacing='0' border='0'>
                                <tr>
                                    <td bgcolor='{$avisoFundo}' style='background-color:{$avisoFundo};border-left:4px solid {$verde};border-radius:6px;padding:14px 16px;'>
                                        <p style='margin:0;font-family:{$fCodigo};font-size:12px;line-height:1.6;color:{$texto};'>
                                            O link vale por <strong style='color:{$verdeEscuro};'>30 minutos</strong> e só pode ser usado uma vez.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Link alternativo -->
                            <p style='margin:26px 0 6px 0;font-family:{$fCorpo};font-size:13px;line-height:1.6;color:{$textoSuave};'>
                                Se o botão não funcionar, copie e cole este endereço no navegador:
                            </p>
                            <p style='margin:0;font-family:{$fCodigo};font-size:12px;line-height:1.6;word-break:break-all;'>
                                <a href='{$linkSeguro}' target='_blank' style='color:{$verdeEscuro};text-decoration:underline;'>{$linkSeguro}</a>
                            </p>

                        </td>
                    </tr>

                    <!-- Rodapé (sobre o fundo escuro) -->
                    <tr>
                        <td align='center' bgcolor='{$fundo}' style='background-color:{$fundo};padding:28px 12px 0 12px;'>
                            <p style='margin:0 0 6px 0;font-family:{$fCorpo};font-size:12px;line-height:1.6;color:#ffffff;-webkit-text-fill-color:#ffffff;'>
                                <font color='#ffffff'>Se você não pediu isso, ignore este e-mail: sua senha continua a mesma.</font>
                            </p>
                            <p style='margin:0;font-family:{$fCodigo};font-size:11px;color:#ffffff;-webkit-text-fill-color:#ffffff;'>
                                <font color='#ffffff'>&copy; {$ano}</font>
                                <span style='color:{$verde};-webkit-text-fill-color:{$verde};'><font color='{$verde}'>Syntax Labs</font></span>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

</body>
</html>";
    }
}