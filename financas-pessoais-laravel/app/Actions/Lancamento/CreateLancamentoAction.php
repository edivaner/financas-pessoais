<?php

namespace App\Actions\Lancamento;

use App\Models\Lancamento;
use App\Models\Conta;
use App\Models\Cartao;
use App\Models\Limite;
use App\Domain\Common\TipoLancamento;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CreateLancamentoAction
{
    public function execute(array $data): Lancamento
    {
        return DB::transaction(function () use ($data) {
            $data['esta_pago'] = $data['data'] <= Carbon::today();
            
            // Se for parcelamento, criar múltiplos lançamentos
            if (isset($data['parcela_total']) && $data['parcela_total'] > 1) {
                return $this->createParcelado($data);
            }

            $lancamento = Lancamento::create($data);

            // Aplicar movimentações se estiver pago
            if ($lancamento->esta_pago) {
                $this->applyMovements($lancamento);
            }

            return $lancamento;
        });
    }

    private function createParcelado(array $data): Lancamento
    {
        $parcelaTotal = $data['parcela_total'];
        $valorParcela = $data['valor'] / $parcelaTotal;
        $dataInicial = Carbon::parse($data['data']);

        $primeiroLancamento = null;

        for ($i = 1; $i <= $parcelaTotal; $i++) {
            $lancamentoData = $data;
            $lancamentoData['valor'] = $valorParcela;
            $lancamentoData['parcela_atual'] = $i;
            $lancamentoData['data'] = $dataInicial->copy()->addMonths($i - 1)->format('Y-m-d');
            $lancamentoData['esta_pago'] = $lancamentoData['data'] <= Carbon::today();

            $lancamento = Lancamento::create($lancamentoData);

            if ($i === 1) {
                $primeiroLancamento = $lancamento;
            }

            // Aplicar movimentações se estiver pago
            if ($lancamento->esta_pago) {
                $this->applyMovements($lancamento);
            }
        }

        return $primeiroLancamento;
    }

    private function applyMovements(Lancamento $lancamento): void
    {
        if ($lancamento->simulado) {
            return; // Não aplica movimentações reais para simulados
        }

        switch ($lancamento->tipo_lancamento) {
            case TipoLancamento::RECEITAS:
                $this->applyReceita($lancamento);
                break;
            case TipoLancamento::DESPESAS:
                $this->applyDespesa($lancamento);
                break;
            case TipoLancamento::TRANSFERENCIA:
                $this->applyTransferencia($lancamento);
                break;
            case TipoLancamento::INVESTIMENTOS:
                $this->applyInvestimento($lancamento);
                break;
        }

        // Atualizar limites se for despesa com categoria
        if ($lancamento->tipo_lancamento === TipoLancamento::DESPESAS && $lancamento->categoria_id) {
            $this->updateLimite($lancamento);
        }
    }

    private function applyReceita(Lancamento $lancamento): void
    {
        if ($lancamento->cartao_id && $lancamento->tipo_cartao === 'CREDITO') {
            // Receita via cartão de crédito
            $cartao = Cartao::find($lancamento->cartao_id);
            $cartao->increment('fatura_total', $lancamento->valor);
        } else {
            // Receita direta na conta
            $conta = Conta::find($lancamento->conta_origem_id);
            $conta->increment('saldo', $lancamento->valor);
        }
    }

    private function applyDespesa(Lancamento $lancamento): void
    {
        if ($lancamento->cartao_id) {
            if ($lancamento->tipo_cartao === 'CREDITO') {
                // Despesa no cartão de crédito
                $cartao = Cartao::find($lancamento->cartao_id);
                $cartao->increment('fatura_total', $lancamento->valor);
            } else {
                // Despesa no cartão de débito (debita da conta)
                $conta = $lancamento->cartao->conta;
                $conta->decrement('saldo', $lancamento->valor);
            }
        } else {
            // Despesa direta na conta
            $conta = Conta::find($lancamento->conta_origem_id);
            $conta->decrement('saldo', $lancamento->valor);
        }
    }

    private function applyTransferencia(Lancamento $lancamento): void
    {
        $contaOrigem = Conta::find($lancamento->conta_origem_id);
        $contaDestino = Conta::find($lancamento->conta_destino_id);

        $contaOrigem->decrement('saldo', $lancamento->valor);
        
        // Verificar se é transferência para saldo investido
        if ($lancamento->conta_destino_id === $lancamento->conta_origem_id) {
            $contaDestino->increment('saldo_investido', $lancamento->valor);
        } else {
            $contaDestino->increment('saldo', $lancamento->valor);
        }
    }

    private function applyInvestimento(Lancamento $lancamento): void
    {
        $conta = Conta::find($lancamento->conta_origem_id);

        if ($lancamento->tipo_cartao === 'CREDITO') {
            // Investimento: debita do saldo investido e adiciona ao saldo
            $conta->decrement('saldo_investido', $lancamento->valor);
            $conta->increment('saldo', $lancamento->valor);
        } else {
            // Investimento: adiciona ao saldo investido
            $conta->increment('saldo_investido', $lancamento->valor);
        }
    }

    private function updateLimite(Lancamento $lancamento): void
    {
        $limite = Limite::where('categoria_id', $lancamento->categoria_id)
            ->where('user_id', $lancamento->user_id)
            ->first();

        if ($limite) {
            $limite->increment('valor_gasto_atual', $lancamento->valor);
        }
    }
}
