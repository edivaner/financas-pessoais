<?php

namespace App\Actions\Limite;

use App\Models\Limite;
use Carbon\Carbon;

class ResetMonthlyLimitsAction
{
    public function execute(): int
    {
        $primeiroDiaMes = Carbon::now()->startOfMonth();
        
        $limites = Limite::where('periodicidade', 'MENSAL')
            ->where(function ($query) use ($primeiroDiaMes) {
                $query->whereNull('ultimo_reset')
                    ->orWhere('ultimo_reset', '<', $primeiroDiaMes);
            })
            ->get();

        $resetados = 0;

        foreach ($limites as $limite) {
            $limite->update([
                'valor_gasto_atual' => 0,
                'ultimo_reset' => $primeiroDiaMes,
            ]);
            
            $resetados++;
        }

        return $resetados;
    }
}
