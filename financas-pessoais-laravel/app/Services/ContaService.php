<?php
namespace App\Services;

use App\Models\Conta;
use App\Models\User;
use App\Repositories\ContaRepository;
use Illuminate\Database\Eloquent\Collection;

class ContaService
{
    public function __construct(private ContaRepository $repo) {}

    public function list(User $user): Collection
    {
        return $this->repo->allByUser($user->id);
    }

    public function create(User $user, array $data): Conta
    {
        return $this->repo->create([...$data, 'user_id' => $user->id]);
    }

    public function update(User $user, string $id, array $data): Conta
    {
        $conta = $this->repo->findByUser($id, $user->id);
        abort_unless($conta, 404, 'Conta não encontrada.');
        return $this->repo->update($conta, $data);
    }

    public function delete(User $user, string $id): void
    {
        $conta = $this->repo->findByUser($id, $user->id);
        abort_unless($conta, 404, 'Conta não encontrada.');
        $this->repo->delete($conta);
    }

    public function find(User $user, string $id): Conta
    {
        $conta = $this->repo->findByUser($id, $user->id);
        abort_unless($conta, 404, 'Conta não encontrada.');
        return $conta;
    }
}
