<?php
namespace App\Repositories;

use App\Models\Cartao;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class CartaoRepository
{
    public function allByUser(string $userId): Collection
    {
        return Cartao::with(['imagem', 'conta'])
            ->where('user_id', $userId)
            ->get();
    }

    public function findByUser(string $id, string $userId): ?Cartao
    {
        return Cartao::with(['imagem', 'conta'])
            ->where('id', $id)
            ->where('user_id', $userId)
            ->first();
    }

    public function create(array $data): Cartao
    {
        $data['id'] = (string) Str::uuid();
        return Cartao::create($data);
    }

    public function update(Cartao $cartao, array $data): Cartao
    {
        $cartao->update($data);
        return $cartao->fresh(['imagem', 'conta']);
    }

    public function delete(Cartao $cartao): void
    {
        $cartao->lancamentos()->delete();
        $cartao->delete();
    }
}
