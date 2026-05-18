<?php

namespace App\Actions\Lancamento;

use App\Models\Lancamento;
use App\Domain\Common\TipoLancamento;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ApplyScheduledMovementsAction
{
    public function execute(): int
    {
        $lancamentos = Lancamento::where('esta_pago', false)
            ->where('data', '<=', Carbon::today())
            ->get();

        $processed = 0;

        foreach ($lancamentos as $lancamento) {
            DB::transaction(function () use ($lancamento, &$processed) {
                $lancamento->update(['esta_pago' => true]);
                
                // Aplicar movimentações
                $createAction = new CreateLancamentoAction();
                $createAction->applyMovements($lancamento);
                
                $processed++;
            });
        }

        return $processed;
    }
}
