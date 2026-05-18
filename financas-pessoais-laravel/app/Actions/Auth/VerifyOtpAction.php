<?php

namespace App\Actions\Auth;

use App\Models\OtpCode;
use App\Models\User;
use App\Domain\Common\PurposeOtp;
use Carbon\Carbon;

class VerifyOtpAction
{
    public function execute(string $email, string $code, PurposeOtp $purpose): array
    {
        $otpCode = OtpCode::where('email', $email)
            ->where('code', strtoupper($code))
            ->where('purpose', $purpose)
            ->where('expires_at', '>', Carbon::now())
            ->whereNull('consumed_at')
            ->first();

        if (!$otpCode) {
            return [
                'success' => false,
                'message' => 'Código inválido ou expirado.',
            ];
        }

        // Marcar código como consumido
        $otpCode->update(['consumed_at' => Carbon::now()]);

        // Buscar usuário
        $user = null;
        if ($otpCode->user_id) {
            $user = User::find($otpCode->user_id);
        }

        return [
            'success' => true,
            'message' => 'Código verificado com sucesso.',
            'user' => $user,
        ];
    }
}
