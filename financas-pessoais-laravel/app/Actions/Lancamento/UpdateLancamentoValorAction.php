<?php

namespace App\Actions\Lancamento;

use App\Models\Lancamento;
use App\Models\Conta;
use App\Models\Cartao;
use App\Models\Limite;
use App\Domain\Common\TipoLancamento;
use Illuminate\Support\Facades\DB;

class UpdateLancamentoValorAction
{
    public function execute(Lancamento $lancamento, float $novoValor): Lancamento
    {
        return DB::transaction(function () use ($lancamento, $novoValor) {
            $valorAnterior = $lancamento->valor;
            $diferenca = $novoValor - $valorAnterior;

            $lancamento->update(['valor' => $novoValor]);

            // Aplicar diferença nas movimentações se estiver pago
            if ($lancamento->esta_pago) {
                $this->applyDifference($lancamento, $diferenca);
            }

            return $lancamento->fresh();
        });
    }

    private function applyDifference(Lancamento $lancamento, float $diferenca): void
    {
        if ($lancamento->simulado) {
            return; // Não aplica movimentações reais para simulados
        }

        switch ($lancamento->tipo_lancamento) {
            case TipoLancamento::RECEITAS:
                $this->applyReceitaDifference($lancamento, $diferenca);
                break;
            case TipoLancamento::DESPESAS:
                $this->applyDespesaDifference($lancamento, $diferenca);
                break;
            case TipoLancamento::TRANSFERENCIA:
                $this->applyTransferenciaDifference($lancamento, $diferenca);
                break;
            case TipoLancamento::INVESTIMENTOS:
                $this->applyInvestimentoDifference($lancamento, $diferenca);
                break;
        }

        // Atualizar limites se for despesa com categoria
        if ($lancamento->tipo_lancamento === TipoLancamento::DESPESAS && $lancamento->categoria_id) {
            $this->updateLimiteDifference($lancamento, $diferenca);
        }
    }

    private function applyReceitaDifference(Lancamento $lancamento, float $diferenca): void
    {
        if ($lancamento->cartao_id && $lancamento->tipo_cartao === 'CREDITO') {
            $cartao = Cartao::find($lancamento->cartao_id);
            $cartao->increment('fatura_total', $diferenca);
        } else {
            $conta = Conta::find($lancamento->conta_origem_id);
            $conta->increment('saldo', $diferenca);
        }
    }

    private function applyDespesaDifference(Lancamento $lancamento, float $diferenca): void
    {
        if ($lancamento->cartao_id) {
            if ($lancamento->tipo_cartao === 'CREDITO') {
                $cartao = Cartao::find($lancamento->cartao_id);
                $cartao->increment('fatura_total', $diferenca);
            } else {
                $conta = $lancamento->cartao->conta;
                $conta->decrement('saldo', $diferenca);
            }
        } else {
            $conta = Conta::find($lancamento->conta_origem_id);
            $conta->decrement('saldo', $diferenca);
        }
    }

    private function applyTransferenciaDifference(Lancamento $lancamento, float $diferenca): void
    {
        $contaOrigem = Conta::find($lancamento->conta_origem_id);
        $contaDestino = Conta::find($lancamento->conta_destino_id);

        $contaOrigem->decrement('saldo', $diferenca);
        
        if ($lancamento->conta_destino_id === $lancamento->conta_origem_id) {
            $contaDestino->increment('saldo_investido', $diferenca);
        } else {
            $contaDestino->increment('saldo', $diferenca);
        }
    }

    private function applyInvestimentoDifference(Lancamento $lancamento, float $diferenca): void
    {
        $conta = Conta::find($lancamento->conta_origem_id);

        if ($lancamento->tipo_cartao === 'CREDITO') {
            $conta->decrement('saldo_investido', $diferenca);
            $conta->increment('saldo', $diferenca);
        } else {
            $conta->increment('saldo_investido', $diferenca);
        }
    }

    private function updateLimiteDifference(Lancamento $lancamento, float $diferenca): void
    {
        $limite = Limite::where('categoria_id', $lancamento->categoria_id)
            ->where('user_id', $lancamento->user_id)
            ->first();

        if ($limite) {
            $limite->increment('valor_gasto_atual', $diferenca);
        }
    }
}
