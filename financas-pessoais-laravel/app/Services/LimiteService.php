<?php
namespace App\Services;

use App\Models\Limite;
use App\Models\User;
use App\Repositories\LimiteRepository;
use Illuminate\Database\Eloquent\Collection;

class LimiteService
{
    public function __construct(private LimiteRepository $repo) {}

    public function list(User $user): Collection
    {
        return $this->repo->allByUser($user->id);
    }

    public function create(User $user, array $data): Limite
    {
        return $this->repo->create([...$data, 'user_id' => $user->id, 'valor_gasto_atual' => 0]);
    }

    public function update(User $user, string $id, array $data): Limite
    {
        $limite = $this->repo->findByUser($id, $user->id);
        abort_unless($limite, 404, 'Limite não encontrado.');
        return $this->repo->update($limite, $data);
    }

    public function delete(User $user, string $id): void
    {
        $limite = $this->repo->findByUser($id, $user->id);
        abort_unless($limite, 404, 'Limite não encontrado.');
        $this->repo->delete($limite);
    }

    public function addGasto(string $categoriaId, string $userId, float $valor): void
    {
        $limite = $this->repo->findByCategoriaAndUser($categoriaId, $userId);
        if ($limite) {
            $limite->increment('valor_gasto_atual', $valor);
        }
    }

    public function subtractGasto(string $categoriaId, string $userId, float $valor): void
    {
        $limite = $this->repo->findByCategoriaAndUser($categoriaId, $userId);
        if ($limite) {
            $limite->decrement('valor_gasto_atual', $valor);
        }
    }

    public function resetMonthly(): void
    {
        $this->repo->resetAll();
    }
}
