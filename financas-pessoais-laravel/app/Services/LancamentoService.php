<?php
// app/Services/LancamentoService.php
namespace App\Services;

use App\Domain\Common\TipoLancamento;
use App\Models\Conta;
use App\Models\Cartao;
use App\Models\Lancamento;
use App\Models\User;
use App\Repositories\LancamentoRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class LancamentoService
{
    public function __construct(
        private LancamentoRepository $repo,
        private LimiteService $limiteService
    ) {}

    public function list(User $user, array $filters = []): Collection
    {
        return $this->repo->listByUser($user->id, $filters);
    }

    public function create(User $user, array $data): Lancamento
    {
        return DB::transaction(function () use ($user, $data) {
            $data['user_id']   = $user->id;
            $data['esta_pago'] = Carbon::parse($data['data'])->lte(Carbon::today());

            if (isset($data['parcela_total']) && (int)$data['parcela_total'] > 1) {
                return $this->createParcelado($data);
            }

            $lancamento = $this->repo->create($data);

            if ($lancamento->esta_pago) {
                $this->applyMovements($lancamento);
            }

            return $lancamento;
        });
    }

    public function update(User $user, string $id, array $data): Lancamento
    {
        return DB::transaction(function () use ($user, $id, $data) {
            $lancamento = $this->repo->findByUser($id, $user->id);
            abort_unless($lancamento, 404, 'Lançamento não encontrado.');

            if ($lancamento->esta_pago) {
                $this->reverseMovements($lancamento);
                if ($lancamento->tipo_lancamento === TipoLancamento::DESPESAS && $lancamento->categoria_id) {
                    $this->limiteService->subtractGasto($lancamento->categoria_id, $user->id, (float)$lancamento->valor);
                }
            }

            $data['esta_pago'] = Carbon::parse($data['data'] ?? $lancamento->data)->lte(Carbon::today());
            $updated = $this->repo->update($lancamento, $data);

            if ($updated->esta_pago) {
                $this->applyMovements($updated);
            }

            return $updated;
        });
    }

    public function delete(User $user, string $id): void
    {
        DB::transaction(function () use ($user, $id) {
            $lancamento = $this->repo->findByUser($id, $user->id);
            abort_unless($lancamento, 404, 'Lançamento não encontrado.');

            if ($lancamento->esta_pago) {
                $this->reverseMovements($lancamento);
                if ($lancamento->tipo_lancamento === TipoLancamento::DESPESAS && $lancamento->categoria_id) {
                    $this->limiteService->subtractGasto($lancamento->categoria_id, $user->id, (float)$lancamento->valor);
                }
            }

            $this->repo->softDelete($lancamento);
        });
    }

    // --- Private helpers ---

    private function createParcelado(array $data): Lancamento
    {
        $total        = (int) $data['parcela_total'];
        $valorParcela = round((float)$data['valor'] / $total, 4);
        $dataInicial  = Carbon::parse($data['data']);
        $first        = null;

        for ($i = 1; $i <= $total; $i++) {
            $parcelaData = [...$data,
                'valor'         => $valorParcela,
                'parcela_atual' => $i,
                'data'          => $dataInicial->copy()->addMonths($i - 1)->toDateString(),
            ];
            $parcelaData['esta_pago'] = Carbon::parse($parcelaData['data'])->lte(Carbon::today());

            $lancamento = $this->repo->create($parcelaData);

            if ($lancamento->esta_pago) {
                $this->applyMovements($lancamento);
            }

            if ($i === 1) $first = $lancamento;
        }

        return $first;
    }

    private function applyMovements(Lancamento $l): void
    {
        match ($l->tipo_lancamento) {
            TipoLancamento::RECEITAS      => $this->applyReceita($l),
            TipoLancamento::DESPESAS      => $this->applyDespesa($l),
            TipoLancamento::TRANSFERENCIA => $this->applyTransferencia($l),
            TipoLancamento::INVESTIMENTOS => $this->applyInvestimento($l),
        };

        if ($l->tipo_lancamento === TipoLancamento::DESPESAS && $l->categoria_id) {
            $this->limiteService->addGasto($l->categoria_id, $l->user_id, (float)$l->valor);
        }
    }

    private function reverseMovements(Lancamento $l): void
    {
        match ($l->tipo_lancamento) {
            TipoLancamento::RECEITAS      => $this->reverseReceita($l),
            TipoLancamento::DESPESAS      => $this->reverseDespesa($l),
            TipoLancamento::TRANSFERENCIA => $this->reverseTransferencia($l),
            TipoLancamento::INVESTIMENTOS => $this->reverseInvestimento($l),
        };
    }

    private function applyReceita(Lancamento $l): void
    {
        $conta = Conta::find($l->conta_origem_id);
        if ($conta) $conta->increment('saldo', $l->valor);
    }

    private function reverseReceita(Lancamento $l): void
    {
        $conta = Conta::find($l->conta_origem_id);
        if ($conta) $conta->decrement('saldo', $l->valor);
    }

    private function applyDespesa(Lancamento $l): void
    {
        if ($l->cartao_id) {
            $cartao = Cartao::find($l->cartao_id);
            if (!$cartao) return;

            if ($l->tipo_cartao === 'CREDITO') {
                $cartao->increment('fatura_total', $l->valor);
            } else {
                $cartao->conta?->decrement('saldo', $l->valor);
            }
        } else {
            $conta = Conta::find($l->conta_origem_id);
            if ($conta) $conta->decrement('saldo', $l->valor);
        }
    }

    private function reverseDespesa(Lancamento $l): void
    {
        if ($l->cartao_id) {
            $cartao = Cartao::find($l->cartao_id);
            if (!$cartao) return;

            if ($l->tipo_cartao === 'CREDITO') {
                $cartao->decrement('fatura_total', $l->valor);
            } else {
                $cartao->conta?->increment('saldo', $l->valor);
            }
        } else {
            $conta = Conta::find($l->conta_origem_id);
            if ($conta) $conta->increment('saldo', $l->valor);
        }
    }

    private function applyTransferencia(Lancamento $l): void
    {
        $origem = Conta::find($l->conta_origem_id);
        if ($origem) $origem->decrement('saldo', $l->valor);

        if (!empty($l->para_saldo_investido)) {
            if ($origem) $origem->increment('saldo_investido', $l->valor);
        } else {
            $destino = Conta::find($l->conta_destino_id);
            if ($destino) $destino->increment('saldo', $l->valor);
        }
    }

    private function reverseTransferencia(Lancamento $l): void
    {
        $origem = Conta::find($l->conta_origem_id);
        if ($origem) $origem->increment('saldo', $l->valor);

        if (!empty($l->para_saldo_investido)) {
            if ($origem) $origem->decrement('saldo_investido', $l->valor);
        } else {
            $destino = Conta::find($l->conta_destino_id);
            if ($destino) $destino->decrement('saldo', $l->valor);
        }
    }

    private function applyInvestimento(Lancamento $l): void
    {
        $conta = Conta::find($l->conta_origem_id);
        if (!$conta) return;

        if ($l->tipo_cartao === 'INVESTIR') {
            $conta->decrement('saldo', $l->valor);
            $conta->increment('saldo_investido', $l->valor);
        } else {
            $conta->decrement('saldo_investido', $l->valor);
            $conta->increment('saldo', $l->valor);
        }
    }

    private function reverseInvestimento(Lancamento $l): void
    {
        $conta = Conta::find($l->conta_origem_id);
        if (!$conta) return;

        if ($l->tipo_cartao === 'INVESTIR') {
            $conta->increment('saldo', $l->valor);
            $conta->decrement('saldo_investido', $l->valor);
        } else {
            $conta->increment('saldo_investido', $l->valor);
            $conta->decrement('saldo', $l->valor);
        }
    }
}
