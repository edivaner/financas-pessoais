<?php
// app/Services/DashboardService.php
namespace App\Services;

use App\Models\User;
use App\Models\Conta;
use App\Models\Cartao;
use App\Models\Lancamento;
use App\Models\Limite;
use App\Domain\Common\TipoLancamento;
use Carbon\Carbon;

class DashboardService
{
    public function getData(User $user): array
    {
        $now   = Carbon::now();
        $mes   = $now->month;
        $ano   = $now->year;

        $contas  = Conta::with('imagem')
            ->where('user_id', $user->id)
            ->get();

        $cartoes = Cartao::with(['imagem', 'conta'])
            ->where('user_id', $user->id)
            ->get();

        $limites = Limite::with('categoria')
            ->where('user_id', $user->id)
            ->get();

        // Lançamentos do mês atual
        $lancamentosMes = Lancamento::where('user_id', $user->id)
            ->whereMonth('data', $mes)
            ->whereYear('data', $ano)
            ->where('simulado', false)
            ->get();

        $receitas  = $lancamentosMes->where('tipo_lancamento', TipoLancamento::RECEITAS)->sum('valor');
        $despesas  = $lancamentosMes->where('tipo_lancamento', TipoLancamento::DESPESAS)->sum('valor');
        $pago      = $lancamentosMes->where('tipo_lancamento', TipoLancamento::DESPESAS)->where('esta_pago', true)->sum('valor');
        $pendente  = $lancamentosMes->where('tipo_lancamento', TipoLancamento::DESPESAS)->where('esta_pago', false)->sum('valor');

        $saldoTotal     = $contas->where('somar_tela_inicial', true)->sum('saldo');
        $investidoTotal = $contas->where('carteira', false)->sum('saldo_investido');

        return [
            'periodo'   => ['mes' => $mes, 'ano' => $ano],
            'totais'    => [
                'saldo_disponivel' => round((float)$saldoTotal, 2),
                'receitas'         => round((float)$receitas, 2),
                'despesas'         => round((float)$despesas, 2),
                'pago'             => round((float)$pago, 2),
                'pendente'         => round((float)$pendente, 2),
                'saldo_investido'  => round((float)$investidoTotal, 2),
            ],
            'contas'    => $contas->where('somar_tela_inicial', true)->values(),
            'cartoes'   => $cartoes,
            'limites'   => $limites,
        ];
    }
}
