<?php
namespace App\Services;

use App\Models\Subcategoria;
use App\Models\User;
use App\Repositories\SubcategoriaRepository;
use Illuminate\Database\Eloquent\Collection;

class SubcategoriaService
{
    public function __construct(private SubcategoriaRepository $repo) {}

    public function list(User $user): Collection
    {
        return $this->repo->allByUser($user->id);
    }

    public function create(User $user, array $data): Subcategoria
    {
        return $this->repo->create([...$data, 'user_id' => $user->id]);
    }

    public function update(User $user, string $id, array $data): Subcategoria
    {
        $sub = $this->repo->findByUser($id, $user->id);
        abort_unless($sub, 404, 'Subcategoria não encontrada.');
        return $this->repo->update($sub, $data);
    }

    public function delete(User $user, string $id): void
    {
        $sub = $this->repo->findByUser($id, $user->id);
        abort_unless($sub, 404, 'Subcategoria não encontrada.');
        $this->repo->delete($sub);
    }
}
