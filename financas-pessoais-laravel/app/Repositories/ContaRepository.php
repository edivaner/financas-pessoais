<?php
namespace App\Repositories;

use App\Models\Conta;
use Illuminate\Database\Eloquent\Collection;

class ContaRepository
{
    public function allByUser(string $userId): Collection
    {
        return Conta::with('imagem')
            ->where('user_id', $userId)
            ->get();
    }

    public function findByUser(string $id, string $userId): ?Conta
    {
        return Conta::with('imagem')
            ->where('id', $id)
            ->where('user_id', $userId)
            ->first();
    }

    public function create(array $data): Conta
    {
        return Conta::create($data);
    }

    public function update(Conta $conta, array $data): Conta
    {
        $conta->update($data);
        return $conta->fresh('imagem');
    }

    public function delete(Conta $conta): void
    {
        $conta->lancamentosOrigem()->delete();
        $conta->lancamentosDestino()->delete();
        $conta->delete();
    }
}
