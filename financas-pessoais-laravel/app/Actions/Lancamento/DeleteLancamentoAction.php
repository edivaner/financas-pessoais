<?php

namespace App\Actions\Lancamento;

use App\Models\Lancamento;
use App\Models\Conta;
use App\Models\Cartao;
use App\Models\Limite;
use App\Domain\Common\TipoLancamento;
use Illuminate\Support\Facades\DB;

class DeleteLancamentoAction
{
    public function execute(Lancamento $lancamento): bool
    {
        return DB::transaction(function () use ($lancamento) {
            // Reverter movimentações se estiver pago
            if ($lancamento->esta_pago) {
                $this->revertMovements($lancamento);
            }

            // Excluir imagens vinculadas
            if ($lancamento->imagem_id) {
                $lancamento->imagem->delete();
            }

            return $lancamento->delete();
        });
    }

    private function revertMovements(Lancamento $lancamento): void
    {
        if ($lancamento->simulado) {
            return; // Não reverte movimentações simuladas
        }

        switch ($lancamento->tipo_lancamento) {
            case TipoLancamento::RECEITAS:
                $this->revertReceita($lancamento);
                break;
            case TipoLancamento::DESPESAS:
                $this->revertDespesa($lancamento);
                break;
            case TipoLancamento::TRANSFERENCIA:
                $this->revertTransferencia($lancamento);
                break;
            case TipoLancamento::INVESTIMENTOS:
                $this->revertInvestimento($lancamento);
                break;
        }

        // Reverter limite se for despesa com categoria
        if ($lancamento->tipo_lancamento === TipoLancamento::DESPESAS && $lancamento->categoria_id) {
            $this->revertLimite($lancamento);
        }
    }

    private function revertReceita(Lancamento $lancamento): void
    {
        if ($lancamento->cartao_id && $lancamento->tipo_cartao === 'CREDITO') {
            // Reverter receita via cartão de crédito
            $cartao = Cartao::find($lancamento->cartao_id);
            $cartao->decrement('fatura_total', $lancamento->valor);
        } else {
            // Reverter receita direta na conta
            $conta = Conta::find($lancamento->conta_origem_id);
            $conta->decrement('saldo', $lancamento->valor);
        }
    }

    private function revertDespesa(Lancamento $lancamento): void
    {
        if ($lancamento->cartao_id) {
            if ($lancamento->tipo_cartao === 'CREDITO') {
                // Reverter despesa no cartão de crédito
                $cartao = Cartao::find($lancamento->cartao_id);
                $cartao->decrement('fatura_total', $lancamento->valor);
            } else {
                // Reverter despesa no cartão de débito
                $conta = $lancamento->cartao->conta;
                $conta->increment('saldo', $lancamento->valor);
            }
        } else {
            // Reverter despesa direta na conta
            $conta = Conta::find($lancamento->conta_origem_id);
            $conta->increment('saldo', $lancamento->valor);
        }
    }

    private function revertTransferencia(Lancamento $lancamento): void
    {
        $contaOrigem = Conta::find($lancamento->conta_origem_id);
        $contaDestino = Conta::find($lancamento->conta_destino_id);

        $contaOrigem->increment('saldo', $lancamento->valor);
        
        // Verificar se é transferência para saldo investido
        if ($lancamento->conta_destino_id === $lancamento->conta_origem_id) {
            $contaDestino->decrement('saldo_investido', $lancamento->valor);
        } else {
            $contaDestino->decrement('saldo', $lancamento->valor);
        }
    }

    private function revertInvestimento(Lancamento $lancamento): void
    {
        $conta = Conta::find($lancamento->conta_origem_id);

        if ($lancamento->tipo_cartao === 'CREDITO') {
            // Reverter investimento: adiciona ao saldo investido e remove do saldo
            $conta->increment('saldo_investido', $lancamento->valor);
            $conta->decrement('saldo', $lancamento->valor);
        } else {
            // Reverter investimento: remove do saldo investido
            $conta->decrement('saldo_investido', $lancamento->valor);
        }
    }

    private function revertLimite(Lancamento $lancamento): void
    {
        $limite = Limite::where('categoria_id', $lancamento->categoria_id)
            ->where('user_id', $lancamento->user_id)
            ->first();

        if ($limite) {
            $limite->decrement('valor_gasto_atual', $lancamento->valor);
        }
    }
}
