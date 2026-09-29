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

            $mail->isHTML(true);
            $mail->Subject = 'Recuperação de senha - Syntax Labs';

            $nomeSeguro = htmlspecialchars($nome, ENT_QUOTES, 'UTF-8');
            $linkSeguro = htmlspecialchars($link, ENT_QUOTES, 'UTF-8');

            $mail->Body = "
                <div style='font-family:Arial,sans-serif;max-width:480px;margin:auto;'>
                    <h2>Olá, {$nomeSeguro}!</h2>
                    <p>Recebemos um pedido para redefinir a sua senha.</p>
                    <p>Clique no botão abaixo para criar uma nova senha. O link vale por 30 minutos e só pode ser usado uma vez.</p>
                    <p style='text-align:center;margin:28px 0;'>
                        <a href='{$linkSeguro}'
                           style='background:#5b4bff;color:#fff;padding:12px 24px;border-radius:8px;text-decoration:none;'>
                           Redefinir senha
                        </a>
                    </p>
                    <p style='font-size:13px;color:#666;'>Se o botão não funcionar, copie e cole este endereço no navegador:<br>{$linkSeguro}</p>
                    <p style='font-size:13px;color:#666;'>Se você não pediu isso, ignore este e-mail: sua senha continua a mesma.</p>
                </div>";

            $mail->AltBody = "Olá, {$nome}!\n\nPara redefinir sua senha, acesse (vale por 30 minutos):\n{$link}\n\nSe você não pediu isso, ignore este e-mail.";

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log('Erro ao enviar e-mail: ' . $mail->ErrorInfo);
            return false;
        }
    }
}