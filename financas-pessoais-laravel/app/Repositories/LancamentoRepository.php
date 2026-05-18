<?php
// app/Repositories/LancamentoRepository.php
namespace App\Repositories;

use App\Models\Lancamento;
use Illuminate\Database\Eloquent\Collection;

class LancamentoRepository
{
    public function listByUser(string $userId, array $filters = []): Collection
    {
        $q = Lancamento::with(['contaOrigem', 'contaDestino', 'cartao', 'categoria', 'subcategoria', 'imagem'])
            ->where('user_id', $userId)
            ->where('simulado', false);

        if (!empty($filters['mes'])) {
            $q->whereMonth('data', $filters['mes']);
        }
        if (!empty($filters['ano'])) {
            $q->whereYear('data', $filters['ano']);
        }
        if (!empty($filters['conta_id'])) {
            $q->where(fn($sq) => $sq
                ->where('conta_origem_id', $filters['conta_id'])
                ->orWhere('conta_destino_id', $filters['conta_id'])
            );
        }
        if (!empty($filters['cartao_id'])) {
            $q->where('cartao_id', $filters['cartao_id']);
        }
        if (!empty($filters['tipo'])) {
            $q->where('tipo_lancamento', $filters['tipo']);
        }
        if (isset($filters['saldo_investido']) && $filters['saldo_investido']) {
            $q->where('tipo_lancamento', 'INVESTIMENTOS');
        }

        return $q->orderBy('data', 'desc')->get();
    }

    public function findByUser(string $id, string $userId): ?Lancamento
    {
        return Lancamento::with(['contaOrigem', 'contaDestino', 'cartao', 'categoria', 'subcategoria', 'imagem'])
            ->where('id', $id)
            ->where('user_id', $userId)
            ->first();
    }

    public function create(array $data): Lancamento
    {
        return Lancamento::create($data);
    }

    public function update(Lancamento $l, array $data): Lancamento
    {
        $l->update($data);
        return $l->fresh(['contaOrigem', 'contaDestino', 'cartao', 'categoria', 'subcategoria', 'imagem']);
    }

    public function softDelete(Lancamento $l): void
    {
        $l->delete();
    }
}
