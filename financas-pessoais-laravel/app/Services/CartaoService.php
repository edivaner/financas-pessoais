<?php
namespace App\Services;

use App\Models\Cartao;
use App\Models\User;
use App\Repositories\CartaoRepository;
use Illuminate\Database\Eloquent\Collection;

class CartaoService
{
    public function __construct(private CartaoRepository $repo) {}

    public function list(User $user): Collection
    {
        return $this->repo->allByUser($user->id);
    }

    public function create(User $user, array $data): Cartao
    {
        return $this->repo->create([...$data, 'user_id' => $user->id, 'fatura_total' => 0]);
    }

    public function update(User $user, string $id, array $data): Cartao
    {
        $cartao = $this->repo->findByUser($id, $user->id);
        abort_unless($cartao, 404, 'Cartão não encontrado.');
        return $this->repo->update($cartao, $data);
    }

    public function delete(User $user, string $id): void
    {
        $cartao = $this->repo->findByUser($id, $user->id);
        abort_unless($cartao, 404, 'Cartão não encontrado.');
        $this->repo->delete($cartao);
    }

    public function find(User $user, string $id): Cartao
    {
        $cartao = $this->repo->findByUser($id, $user->id);
        abort_unless($cartao, 404, 'Cartão não encontrado.');
        return $cartao;
    }
}
