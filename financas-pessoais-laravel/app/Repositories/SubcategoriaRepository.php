<?php
namespace App\Repositories;

use App\Models\Subcategoria;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class SubcategoriaRepository
{
    public function allByUser(string $userId): Collection
    {
        return Subcategoria::with('categoria')
            ->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)->orWhereNull('user_id');
            })
            ->orderBy('nome')
            ->get();
    }

    public function findByUser(string $id, string $userId): ?Subcategoria
    {
        return Subcategoria::with('categoria')
            ->where('id', $id)
            ->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)->orWhereNull('user_id');
            })
            ->first();
    }

    public function create(array $data): Subcategoria
    {
        $data['id'] = (string) Str::uuid();
        return Subcategoria::create($data);
    }

    public function update(Subcategoria $sub, array $data): Subcategoria
    {
        $sub->update($data);
        return $sub->fresh('categoria');
    }

    public function delete(Subcategoria $sub): void
    {
        $hasLancamentos = $sub->lancamentos()->exists();
        abort_if($hasLancamentos, 422, 'Subcategoria possui lançamentos e não pode ser excluída.');
        $sub->delete();
    }
}
