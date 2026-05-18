<?php
namespace App\Repositories;

use App\Models\Limite;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class LimiteRepository
{
    public function allByUser(string $userId): Collection
    {
        return Limite::with('categoria')
            ->where('user_id', $userId)
            ->get();
    }

    public function findByUser(string $id, string $userId): ?Limite
    {
        return Limite::with('categoria')
            ->where('id', $id)
            ->where('user_id', $userId)
            ->first();
    }

    public function findByCategoriaAndUser(string $categoriaId, string $userId): ?Limite
    {
        return Limite::where('categoria_id', $categoriaId)
            ->where('user_id', $userId)
            ->first();
    }

    public function create(array $data): Limite
    {
        $data['id'] = (string) Str::uuid();
        return Limite::create($data);
    }

    public function update(Limite $limite, array $data): Limite
    {
        $limite->update($data);
        return $limite->fresh('categoria');
    }

    public function delete(Limite $limite): void
    {
        $limite->delete();
    }

    public function resetAll(): void
    {
        Limite::query()->update(['valor_gasto_atual' => 0]);
    }
}
