<?php

namespace App\Services;

use App\Domain\Common\PurposeOtp;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Message;

class MailService
{
    public function sendOtp(string $email, string $code, PurposeOtp $purpose): void
    {
        $subject = $this->getSubject($purpose);
        $message = $this->getMessage($code, $purpose);

        Mail::raw($message, function (Message $mail) use ($email, $subject) {
            $mail->to($email)
                ->subject($subject);
        });
    }

    private function getSubject(PurposeOtp $purpose): string
    {
        return match ($purpose) {
            PurposeOtp::SIGNUP => 'Código de Verificação - Cadastro',
            PurposeOtp::LOGIN => 'Código de Verificação - Login',
            PurposeOtp::RESET => 'Código de Verificação - Redefinir Senha',
        };
    }

    private function getMessage(string $code, PurposeOtp $purpose): string
    {
        $action = match ($purpose) {
            PurposeOtp::SIGNUP => 'cadastro',
            PurposeOtp::LOGIN => 'login',
            PurposeOtp::RESET => 'redefinição de senha',
        };

        return "Seu código de verificação para {$action} é: {$code}\n\n" .
               "Este código expira em 10 minutos.\n\n" .
               "Se você não solicitou este código, ignore este e-mail.";
    }
}
