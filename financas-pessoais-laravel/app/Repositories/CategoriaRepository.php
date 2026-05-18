<?php
namespace App\Repositories;

use App\Models\Categoria;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class CategoriaRepository
{
    public function allByUser(string $userId): Collection
    {
        return Categoria::where(function ($q) use ($userId) {
            $q->where('user_id', $userId)->orWhereNull('user_id');
        })->orderBy('tipo')->orderBy('nome')->get();
    }

    public function findByUser(string $id, string $userId): ?Categoria
    {
        return Categoria::where('id', $id)
            ->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)->orWhereNull('user_id');
            })->first();
    }

    public function create(array $data): Categoria
    {
        $data['id'] = (string) Str::uuid();
        return Categoria::create($data);
    }

    public function update(Categoria $cat, array $data): Categoria
    {
        $cat->update($data);
        return $cat->fresh();
    }

    public function delete(Categoria $cat): void
    {
        $hasLancamentos = $cat->lancamentos()->exists();
        abort_if($hasLancamentos, 422, 'Categoria possui lançamentos vinculados e não pode ser excluída.');
        $cat->delete();
    }
}
