<?php
namespace App\Services;

use App\Models\Categoria;
use App\Models\User;
use App\Repositories\CategoriaRepository;
use Illuminate\Database\Eloquent\Collection;

class CategoriaService
{
    public function __construct(private CategoriaRepository $repo) {}

    public function list(User $user): Collection
    {
        return $this->repo->allByUser($user->id);
    }

    public function create(User $user, array $data): Categoria
    {
        return $this->repo->create([...$data, 'user_id' => $user->id]);
    }

    public function update(User $user, string $id, array $data): Categoria
    {
        $cat = $this->repo->findByUser($id, $user->id);
        abort_unless($cat, 404, 'Categoria não encontrada.');
        return $this->repo->update($cat, $data);
    }

    public function delete(User $user, string $id): void
    {
        $cat = $this->repo->findByUser($id, $user->id);
        abort_unless($cat, 404, 'Categoria não encontrada.');
        $this->repo->delete($cat);
    }
}
