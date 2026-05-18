<?php

namespace App\Actions\Auth;

use App\Models\OtpCode;
use App\Models\User;
use App\Domain\Common\PurposeOtp;
use App\Services\MailService;
use Carbon\Carbon;
use Illuminate\Support\Str;

class SendOtpAction
{
    public function __construct(
        private MailService $mailService
    ) {}

    public function execute(string $email, PurposeOtp $purpose): array
    {
        // Limpar códigos expirados
        OtpCode::where('email', $email)
            ->where('expires_at', '<', Carbon::now())
            ->delete();

        // Verificar se já existe código válido
        $existingCode = OtpCode::where('email', $email)
            ->where('purpose', $purpose)
            ->where('expires_at', '>', Carbon::now())
            ->whereNull('consumed_at')
            ->first();

        if ($existingCode) {
            return [
                'success' => false,
                'message' => 'Código já enviado. Aguarde 1 minuto antes de solicitar outro.',
                'expires_at' => $existingCode->expires_at,
            ];
        }

        // Gerar novo código
        $code = $this->generateCode();
        $expiresAt = Carbon::now()->addMinutes(10);

        // Buscar usuário se for login
        $user = null;
        if ($purpose === PurposeOtp::LOGIN) {
            $user = User::where('email', $email)->first();
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'Usuário não encontrado.',
                ];
            }
        }

        // Criar registro OTP
        $otpCode = OtpCode::create([
            'user_id' => $user?->id,
            'email' => $email,
            'code' => $code,
            'purpose' => $purpose,
            'expires_at' => $expiresAt,
        ]);

        // Enviar e-mail
        $this->mailService->sendOtp($email, $code, $purpose);

        return [
            'success' => true,
            'message' => 'Código enviado com sucesso.',
            'expires_at' => $expiresAt,
        ];
    }

    private function generateCode(): string
    {
        return strtoupper(Str::random(6));
    }
}
