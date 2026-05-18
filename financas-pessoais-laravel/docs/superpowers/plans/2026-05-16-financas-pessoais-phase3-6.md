# Finanças Pessoais — Implementation Plan (Phases 3–6)

> **For agentic workers:** REQUIRED SUB-SKILL: Use `superpowers:executing-plans`. Prerequisite: Phases 0–2 complete.

**Goal:** Categorias/Limites, Dashboard completo, Lançamentos (core do sistema financeiro).

---

## Phase 3: Categorias + Subcategorias + Limites

### Task 11: Backend — Categoria + Subcategoria + Limite

**Files:**
- Create: `app/Repositories/CategoriaRepository.php`
- Create: `app/Services/CategoriaService.php`
- Create: `app/Http/Controllers/CategoriaController.php`
- Create: `app/Http/Requests/StoreCategoriaRequest.php`
- Create: `app/Http/Requests/UpdateCategoriaRequest.php`
- Create: `app/Repositories/SubcategoriaRepository.php`
- Create: `app/Services/SubcategoriaService.php`
- Create: `app/Http/Controllers/SubcategoriaController.php`
- Create: `app/Http/Requests/StoreSubcategoriaRequest.php`
- Create: `app/Http/Requests/UpdateSubcategoriaRequest.php`
- Create: `app/Repositories/LimiteRepository.php`
- Create: `app/Services/LimiteService.php`
- Create: `app/Http/Controllers/LimiteController.php`
- Create: `app/Http/Requests/StoreLimiteRequest.php`
- Create: `app/Http/Requests/UpdateLimiteRequest.php`
- Modify: `database/seeders/CategoriasSeeder.php`
- Modify: `database/seeders/SubcategoriasSeeder.php`
- Modify: `database/seeders/DatabaseSeeder.php`

- [ ] **Step 1: Create CategoriaRepository**

```php
<?php
// app/Repositories/CategoriaRepository.php
namespace App\Repositories;

use App\Models\Categoria;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class CategoriaRepository
{
    public function allByUser(string $userId): Collection
    {
        return Categoria::where('user_id', $userId)->get();
    }

    public function findByUser(string $id, string $userId): ?Categoria
    {
        return Categoria::where('id', $id)->where('user_id', $userId)->first();
    }

    public function create(array $data): Categoria
    {
        $data['id'] = Str::uuid();
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
```

- [ ] **Step 2: Create CategoriaService**

```php
<?php
// app/Services/CategoriaService.php
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
```

- [ ] **Step 3: Create CategoriaController**

```php
<?php
// app/Http/Controllers/CategoriaController.php
namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoriaRequest;
use App\Http\Requests\UpdateCategoriaRequest;
use App\Services\CategoriaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    public function __construct(private CategoriaService $service) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->service->list($request->user())]);
    }

    public function store(StoreCategoriaRequest $request): JsonResponse
    {
        $cat = $this->service->create($request->user(), $request->validated());
        return response()->json(['data' => $cat, 'message' => 'Categoria criada.'], 201);
    }

    public function update(UpdateCategoriaRequest $request, string $id): JsonResponse
    {
        $cat = $this->service->update($request->user(), $id, $request->validated());
        return response()->json(['data' => $cat, 'message' => 'Categoria atualizada.']);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $this->service->delete($request->user(), $id);
        return response()->json(['message' => 'Categoria excluída.']);
    }
}
```

- [ ] **Step 4: Create Categoria Form Requests**

```php
<?php
// app/Http/Requests/StoreCategoriaRequest.php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoriaRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nome'      => 'required|string|max:100',
            'cor'       => 'required|string|max:20',
            'icone'     => 'required|string|max:100',
            'tipo'      => 'required|in:DESPESAS,CREDITO,INVESTIMENTOS',
            'ativo'     => 'sometimes|boolean',
            'essencial' => 'sometimes|boolean',
        ];
    }
}
```

```php
<?php
// app/Http/Requests/UpdateCategoriaRequest.php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoriaRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nome'      => 'sometimes|string|max:100',
            'cor'       => 'sometimes|string|max:20',
            'icone'     => 'sometimes|string|max:100',
            'tipo'      => 'sometimes|in:DESPESAS,CREDITO,INVESTIMENTOS',
            'ativo'     => 'sometimes|boolean',
            'essencial' => 'sometimes|boolean',
        ];
    }
}
```

- [ ] **Step 5: Create SubcategoriaRepository + SubcategoriaService + SubcategoriaController**

```php
<?php
// app/Repositories/SubcategoriaRepository.php
namespace App\Repositories;

use App\Models\Subcategoria;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class SubcategoriaRepository
{
    public function allByUser(string $userId): Collection
    {
        return Subcategoria::with('categoria')
            ->whereHas('categoria', fn($q) => $q->where('user_id', $userId))
            ->get();
    }

    public function findByUser(string $id, string $userId): ?Subcategoria
    {
        return Subcategoria::with('categoria')
            ->where('id', $id)
            ->whereHas('categoria', fn($q) => $q->where('user_id', $userId))
            ->first();
    }

    public function create(array $data): Subcategoria
    {
        $data['id'] = Str::uuid();
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
```

```php
<?php
// app/Services/SubcategoriaService.php
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
        return $this->repo->create($data);
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
```

```php
<?php
// app/Http/Controllers/SubcategoriaController.php
namespace App\Http\Controllers;

use App\Http\Requests\StoreSubcategoriaRequest;
use App\Http\Requests\UpdateSubcategoriaRequest;
use App\Services\SubcategoriaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubcategoriaController extends Controller
{
    public function __construct(private SubcategoriaService $service) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->service->list($request->user())]);
    }

    public function store(StoreSubcategoriaRequest $request): JsonResponse
    {
        $sub = $this->service->create($request->user(), $request->validated());
        return response()->json(['data' => $sub, 'message' => 'Subcategoria criada.'], 201);
    }

    public function update(UpdateSubcategoriaRequest $request, string $id): JsonResponse
    {
        $sub = $this->service->update($request->user(), $id, $request->validated());
        return response()->json(['data' => $sub, 'message' => 'Subcategoria atualizada.']);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $this->service->delete($request->user(), $id);
        return response()->json(['message' => 'Subcategoria excluída.']);
    }
}
```

```php
<?php
// app/Http/Requests/StoreSubcategoriaRequest.php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubcategoriaRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'categoria_id' => 'required|exists:categorias,id',
            'nome'         => 'required|string|max:100',
            'cor'          => 'required|string|max:20',
            'icone'        => 'required|string|max:100',
            'ativo'        => 'sometimes|boolean',
        ];
    }
}
```

```php
<?php
// app/Http/Requests/UpdateSubcategoriaRequest.php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSubcategoriaRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nome'  => 'sometimes|string|max:100',
            'cor'   => 'sometimes|string|max:20',
            'icone' => 'sometimes|string|max:100',
            'ativo' => 'sometimes|boolean',
        ];
    }
}
```

- [ ] **Step 6: Create LimiteRepository + LimiteService + LimiteController**

```php
<?php
// app/Repositories/LimiteRepository.php
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
        $data['id'] = Str::uuid();
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

    public function resetAllMonthly(string $userId): void
    {
        Limite::where('user_id', $userId)->update(['valor_gasto_atual' => 0]);
    }

    public function resetAll(): void
    {
        Limite::query()->update(['valor_gasto_atual' => 0]);
    }
}
```

```php
<?php
// app/Services/LimiteService.php
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
```

```php
<?php
// app/Http/Controllers/LimiteController.php
namespace App\Http\Controllers;

use App\Http\Requests\StoreLimiteRequest;
use App\Http\Requests\UpdateLimiteRequest;
use App\Services\LimiteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LimiteController extends Controller
{
    public function __construct(private LimiteService $service) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->service->list($request->user())]);
    }

    public function store(StoreLimiteRequest $request): JsonResponse
    {
        $limite = $this->service->create($request->user(), $request->validated());
        return response()->json(['data' => $limite, 'message' => 'Limite criado.'], 201);
    }

    public function update(UpdateLimiteRequest $request, string $id): JsonResponse
    {
        $limite = $this->service->update($request->user(), $id, $request->validated());
        return response()->json(['data' => $limite, 'message' => 'Limite atualizado.']);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $this->service->delete($request->user(), $id);
        return response()->json(['message' => 'Limite excluído.']);
    }
}
```

```php
<?php
// app/Http/Requests/StoreLimiteRequest.php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLimiteRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'categoria_id' => 'required|exists:categorias,id',
            'titulo'       => 'required|string|max:100',
            'descricao'    => 'nullable|string|max:255',
            'valor_limite' => 'required|numeric|min:0.01',
        ];
    }
}
```

```php
<?php
// app/Http/Requests/UpdateLimiteRequest.php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLimiteRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'titulo'       => 'sometimes|string|max:100',
            'descricao'    => 'nullable|string|max:255',
            'valor_limite' => 'sometimes|numeric|min:0.01',
        ];
    }
}
```

- [ ] **Step 7: Write and run seeders**

```php
<?php
// database/seeders/CategoriasSeeder.php
namespace Database\Seeders;

use App\Models\Categoria;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategoriasSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = [
            ['nome' => 'Alimentação',  'cor' => '#f97316', 'icone' => 'bi bi-egg-fried',       'tipo' => 'DESPESAS'],
            ['nome' => 'Transporte',   'cor' => '#3b82f6', 'icone' => 'bi bi-car-front',        'tipo' => 'DESPESAS'],
            ['nome' => 'Moradia',      'cor' => '#8b5cf6', 'icone' => 'bi bi-house',            'tipo' => 'DESPESAS'],
            ['nome' => 'Saúde',        'cor' => '#ef4444', 'icone' => 'bi bi-heart-pulse',      'tipo' => 'DESPESAS'],
            ['nome' => 'Educação',     'cor' => '#06b6d4', 'icone' => 'bi bi-book',             'tipo' => 'DESPESAS'],
            ['nome' => 'Academia',     'cor' => '#ec4899', 'icone' => 'bi bi-bicycle',          'tipo' => 'DESPESAS'],
            ['nome' => 'Lazer',        'cor' => '#84cc16', 'icone' => 'bi bi-controller',       'tipo' => 'DESPESAS'],
            ['nome' => 'Vestuário',    'cor' => '#f59e0b', 'icone' => 'bi bi-bag',              'tipo' => 'DESPESAS'],
            ['nome' => 'Impostos',     'cor' => '#6b7280', 'icone' => 'bi bi-receipt',          'tipo' => 'DESPESAS'],
            ['nome' => 'Salário',      'cor' => '#22c55e', 'icone' => 'bi bi-cash-coin',        'tipo' => 'CREDITO'],
            ['nome' => 'Freelance',    'cor' => '#10b981', 'icone' => 'bi bi-laptop',           'tipo' => 'CREDITO'],
            ['nome' => 'Investimentos','cor' => '#a855f7', 'icone' => 'bi bi-graph-up-arrow',   'tipo' => 'INVESTIMENTOS'],
        ];

        foreach ($categorias as $cat) {
            Categoria::firstOrCreate(
                ['nome' => $cat['nome'], 'user_id' => null],
                [...$cat, 'id' => Str::uuid(), 'ativo' => true, 'essencial' => false, 'user_id' => null]
            );
        }
    }
}
```

```php
<?php
// database/seeders/SubcategoriasSeeder.php
namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Subcategoria;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SubcategoriasSeeder extends Seeder
{
    public function run(): void
    {
        $subs = [
            'Alimentação' => [
                ['nome' => 'Mercado',   'cor' => '#f97316', 'icone' => 'bi bi-cart'],
                ['nome' => 'Delivery',  'cor' => '#fb923c', 'icone' => 'bi bi-scooter'],
                ['nome' => 'Padaria',   'cor' => '#fed7aa', 'icone' => 'bi bi-cup-hot'],
                ['nome' => 'Restaurante','cor' => '#ea580c','icone' => 'bi bi-shop'],
            ],
            'Transporte' => [
                ['nome' => 'Gasolina',  'cor' => '#3b82f6', 'icone' => 'bi bi-fuel-pump'],
                ['nome' => 'Uber/99',   'cor' => '#60a5fa', 'icone' => 'bi bi-car-front'],
                ['nome' => 'Ônibus',    'cor' => '#93c5fd', 'icone' => 'bi bi-bus-front'],
            ],
            'Moradia' => [
                ['nome' => 'Aluguel',   'cor' => '#8b5cf6', 'icone' => 'bi bi-building'],
                ['nome' => 'IPTU',      'cor' => '#a78bfa', 'icone' => 'bi bi-file-text'],
                ['nome' => 'Condomínio','cor' => '#c4b5fd', 'icone' => 'bi bi-buildings'],
                ['nome' => 'Energia',   'cor' => '#7c3aed', 'icone' => 'bi bi-lightning'],
            ],
        ];

        foreach ($subs as $catNome => $items) {
            $cat = Categoria::where('nome', $catNome)->first();
            if (!$cat) continue;
            foreach ($items as $item) {
                Subcategoria::firstOrCreate(
                    ['nome' => $item['nome'], 'categoria_id' => $cat->id],
                    [...$item, 'id' => Str::uuid(), 'ativo' => true]
                );
            }
        }
    }
}
```

```php
<?php
// database/seeders/DatabaseSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CategoriasSeeder::class,
            SubcategoriasSeeder::class,
        ]);
    }
}
```

- [ ] **Step 8: Run migrations + seeders**

```bash
docker compose exec laravel-app php artisan migrate
docker compose exec laravel-app php artisan db:seed
```

Expected: migrations run, 12 categorias and subcategorias inserted.

- [ ] **Step 9: Commit**

```bash
git add app/Repositories/CategoriaRepository.php app/Repositories/SubcategoriaRepository.php app/Repositories/LimiteRepository.php app/Services/CategoriaService.php app/Services/SubcategoriaService.php app/Services/LimiteService.php app/Http/Controllers/CategoriaController.php app/Http/Controllers/SubcategoriaController.php app/Http/Controllers/LimiteController.php app/Http/Requests/StoreCategoriaRequest.php app/Http/Requests/UpdateCategoriaRequest.php app/Http/Requests/StoreSubcategoriaRequest.php app/Http/Requests/UpdateSubcategoriaRequest.php app/Http/Requests/StoreLimiteRequest.php app/Http/Requests/UpdateLimiteRequest.php database/seeders/CategoriasSeeder.php database/seeders/SubcategoriasSeeder.php database/seeders/DatabaseSeeder.php
git commit -m "feat: Categoria, Subcategoria, Limite Service/Repository + seeders"
```

---

### Task 12: Frontend — Categoria/Limite stores + pages + Profile hub

**Files:**
- Create: `resources/js/stores/categoria.ts`
- Create: `resources/js/pages/profile/Categories.vue`
- Create: `resources/js/pages/profile/Subcategories.vue`
- Create: `resources/js/pages/profile/Limits.vue`
- Modify: `resources/js/pages/profile/Profile.vue`

- [ ] **Step 1: Create categoria store**

```typescript
// resources/js/stores/categoria.ts
import { defineStore } from 'pinia'
import { ref } from 'vue'
import axios from 'axios'

export interface Categoria {
  id: string
  nome: string
  cor: string
  icone: string
  tipo: 'DESPESAS' | 'CREDITO' | 'INVESTIMENTOS'
  ativo: boolean
  essencial: boolean
}

export interface Subcategoria {
  id: string
  categoria_id: string
  nome: string
  cor: string
  icone: string
  ativo: boolean
  categoria?: Categoria
}

export interface Limite {
  id: string
  categoria_id: string
  titulo: string
  descricao?: string
  valor_limite: number
  valor_gasto_atual: number
  categoria?: Categoria
}

export const useCategoriaStore = defineStore('categoria', () => {
  const categorias    = ref<Categoria[]>([])
  const subcategorias = ref<Subcategoria[]>([])
  const limites       = ref<Limite[]>([])

  const fetchCategorias    = async () => { const { data } = await axios.get('/api/categorias');    categorias.value = data.data }
  const fetchSubcategorias = async () => { const { data } = await axios.get('/api/subcategorias'); subcategorias.value = data.data }
  const fetchLimites       = async () => { const { data } = await axios.get('/api/limites');       limites.value = data.data }

  const createCategoria = async (payload: object) => {
    const { data } = await axios.post('/api/categorias', payload)
    categorias.value.push(data.data)
    return data.data
  }

  const updateCategoria = async (id: string, payload: object) => {
    const { data } = await axios.put(`/api/categorias/${id}`, payload)
    const idx = categorias.value.findIndex(c => c.id === id)
    if (idx !== -1) categorias.value[idx] = data.data
  }

  const deleteCategoria = async (id: string) => {
    await axios.delete(`/api/categorias/${id}`)
    categorias.value = categorias.value.filter(c => c.id !== id)
  }

  const createSubcategoria = async (payload: object) => {
    const { data } = await axios.post('/api/subcategorias', payload)
    subcategorias.value.push(data.data)
  }

  const updateSubcategoria = async (id: string, payload: object) => {
    const { data } = await axios.put(`/api/subcategorias/${id}`, payload)
    const idx = subcategorias.value.findIndex(s => s.id === id)
    if (idx !== -1) subcategorias.value[idx] = data.data
  }

  const deleteSubcategoria = async (id: string) => {
    await axios.delete(`/api/subcategorias/${id}`)
    subcategorias.value = subcategorias.value.filter(s => s.id !== id)
  }

  const createLimite = async (payload: object) => {
    const { data } = await axios.post('/api/limites', payload)
    limites.value.push(data.data)
  }

  const updateLimite = async (id: string, payload: object) => {
    const { data } = await axios.put(`/api/limites/${id}`, payload)
    const idx = limites.value.findIndex(l => l.id === id)
    if (idx !== -1) limites.value[idx] = data.data
  }

  const deleteLimite = async (id: string) => {
    await axios.delete(`/api/limites/${id}`)
    limites.value = limites.value.filter(l => l.id !== id)
  }

  return {
    categorias, subcategorias, limites,
    fetchCategorias, fetchSubcategorias, fetchLimites,
    createCategoria, updateCategoria, deleteCategoria,
    createSubcategoria, updateSubcategoria, deleteSubcategoria,
    createLimite, updateLimite, deleteLimite,
  }
})
```

- [ ] **Step 2: Create Categories.vue**

```vue
<!-- resources/js/pages/profile/Categories.vue -->
<template>
  <AppLayout>
    <div class="container-fluid px-3 py-3">
      <div class="d-flex align-items-center mb-3">
        <router-link to="/profile" class="btn btn-sm me-2"><i class="bi bi-arrow-left"></i></router-link>
        <h5 class="mb-0 fw-bold">Categorias</h5>
        <button class="btn btn-sm btn-success ms-auto" @click="openCreate">
          <i class="bi bi-plus-lg me-1"></i> Adicionar
        </button>
      </div>

      <div v-for="cat in store.categorias" :key="cat.id" class="card-modern mb-2 d-flex align-items-center">
        <div class="cat-icon me-3" :style="{ background: cat.cor + '22', color: cat.cor }">
          <i :class="cat.icone"></i>
        </div>
        <div class="flex-grow-1">
          <div class="fw-semibold">{{ cat.nome }}</div>
          <small class="text-secondary">{{ cat.tipo }} · {{ cat.ativo ? 'Ativo' : 'Inativo' }}</small>
        </div>
        <div class="d-flex gap-2">
          <button class="btn btn-sm btn-outline-warning" @click="toggleAtivo(cat)">
            <i :class="cat.ativo ? 'bi bi-toggle-on' : 'bi bi-toggle-off'"></i>
          </button>
          <button class="btn btn-sm btn-outline-primary" @click="openEdit(cat)"><i class="bi bi-pencil"></i></button>
          <button class="btn btn-sm btn-outline-danger"  @click="confirmDelete(cat)"><i class="bi bi-trash"></i></button>
        </div>
      </div>
    </div>

    <BOffcanvas v-model="showForm" placement="bottom">
      <template #header><h6 class="mb-0">{{ editing ? 'Editar' : 'Nova' }} Categoria</h6></template>
      <BForm @submit.prevent="submit" class="p-2">
        <div class="mb-3"><label class="form-label">Nome *</label><BFormInput v-model="form.nome" required /></div>
        <div class="mb-3"><label class="form-label">Tipo *</label>
          <BFormSelect v-model="form.tipo" :options="tipoOptions" required />
        </div>
        <div class="mb-3"><label class="form-label">Cor *</label>
          <input type="color" class="form-control form-control-color" v-model="form.cor" />
        </div>
        <div class="mb-3"><label class="form-label">Ícone (bootstrap-icons class)</label>
          <BFormInput v-model="form.icone" placeholder="bi bi-tag" />
        </div>
        <div v-if="formError" class="alert alert-danger py-2">{{ formError }}</div>
        <div class="d-flex gap-2 justify-content-end">
          <BButton variant="outline-secondary" @click="showForm = false">Cancelar</BButton>
          <BButton variant="success" type="submit" :disabled="saving">Salvar</BButton>
        </div>
      </BForm>
    </BOffcanvas>

    <BModal v-model="showDelete" title="Confirmar exclusão" @ok="doDelete">
      <p>Excluir <strong>{{ deleting?.nome }}</strong>? Não é possível excluir se houver lançamentos vinculados.</p>
    </BModal>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import AppLayout from '@/components/layouts/AppLayout.vue'
import { useCategoriaStore, type Categoria } from '@/stores/categoria'

const store      = useCategoriaStore()
const showForm   = ref(false)
const showDelete = ref(false)
const editing    = ref<Categoria | null>(null)
const deleting   = ref<Categoria | null>(null)
const saving     = ref(false)
const formError  = ref('')

const tipoOptions = [
  { value: 'DESPESAS',     text: 'Despesas' },
  { value: 'CREDITO',      text: 'Crédito / Receita' },
  { value: 'INVESTIMENTOS',text: 'Investimentos' },
]

const form = reactive({ nome: '', tipo: 'DESPESAS' as string, cor: '#22c55e', icone: 'bi bi-tag' })

const openCreate = () => {
  editing.value = null
  Object.assign(form, { nome: '', tipo: 'DESPESAS', cor: '#22c55e', icone: 'bi bi-tag' })
  showForm.value = true
}

const openEdit = (cat: Categoria) => {
  editing.value = cat
  Object.assign(form, { nome: cat.nome, tipo: cat.tipo, cor: cat.cor, icone: cat.icone })
  showForm.value = true
}

const toggleAtivo = async (cat: Categoria) => {
  await store.updateCategoria(cat.id, { ativo: !cat.ativo })
}

const confirmDelete = (cat: Categoria) => { deleting.value = cat; showDelete.value = true }

const submit = async () => {
  try {
    saving.value   = true
    formError.value = ''
    if (editing.value) {
      await store.updateCategoria(editing.value.id, { ...form })
    } else {
      await store.createCategoria({ ...form, ativo: true, essencial: false })
    }
    showForm.value = false
  } catch (e: any) {
    formError.value = e.response?.data?.message ?? 'Erro ao salvar'
  } finally {
    saving.value = false
  }
}

const doDelete = async () => {
  if (!deleting.value) return
  try {
    await store.deleteCategoria(deleting.value.id)
  } catch (e: any) {
    alert(e.response?.data?.message ?? 'Erro ao excluir')
  }
  showDelete.value = false
}

onMounted(() => store.fetchCategorias())
</script>

<style scoped lang="scss">
.cat-icon {
  width: 40px; height: 40px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0;
}
</style>
```

- [ ] **Step 3: Create Limits.vue**

```vue
<!-- resources/js/pages/profile/Limits.vue -->
<template>
  <AppLayout>
    <div class="container-fluid px-3 py-3">
      <div class="d-flex align-items-center mb-3">
        <router-link to="/profile" class="btn btn-sm me-2"><i class="bi bi-arrow-left"></i></router-link>
        <h5 class="mb-0 fw-bold">Meus Limites</h5>
        <button class="btn btn-sm btn-success ms-auto" @click="openCreate"><i class="bi bi-plus-lg me-1"></i> Adicionar</button>
      </div>

      <div v-for="limite in store.limites" :key="limite.id" class="card-modern mb-3">
        <LimitBar
          :name="limite.titulo"
          :valor-gasto="Number(limite.valor_gasto_atual)"
          :valor-limite="Number(limite.valor_limite)"
          :icon="limite.categoria?.icone"
          :icon-color="limite.categoria?.cor"
        />
        <div class="d-flex gap-2 mt-2 justify-content-end">
          <button class="btn btn-sm btn-outline-primary" @click="openEdit(limite)"><i class="bi bi-pencil"></i></button>
          <button class="btn btn-sm btn-outline-danger" @click="confirmDelete(limite)"><i class="bi bi-trash"></i></button>
        </div>
      </div>

      <p v-if="!store.limites.length" class="text-center text-secondary py-4">Nenhum limite cadastrado.</p>
    </div>

    <BOffcanvas v-model="showForm" placement="bottom">
      <template #header><h6 class="mb-0">{{ editing ? 'Editar' : 'Novo' }} Limite</h6></template>
      <BForm @submit.prevent="submit" class="p-2">
        <div class="mb-3"><label class="form-label">Título *</label><BFormInput v-model="form.titulo" required /></div>
        <div class="mb-3"><label class="form-label">Categoria *</label>
          <BFormSelect v-model="form.categoria_id" :options="catOptions" required />
        </div>
        <div class="mb-3"><label class="form-label">Valor limite *</label>
          <div class="input-group"><span class="input-group-text">R$</span>
            <BFormInput v-model.number="form.valor_limite" type="number" step="0.01" min="0.01" required />
          </div>
        </div>
        <div class="mb-3"><label class="form-label">Descrição</label><BFormInput v-model="form.descricao" /></div>
        <div class="d-flex gap-2 justify-content-end">
          <BButton variant="outline-secondary" @click="showForm = false">Cancelar</BButton>
          <BButton variant="success" type="submit">Salvar</BButton>
        </div>
      </BForm>
    </BOffcanvas>

    <BModal v-model="showDelete" title="Confirmar" @ok="doDelete">
      <p>Excluir limite <strong>{{ deleting?.titulo }}</strong>?</p>
    </BModal>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue'
import AppLayout from '@/components/layouts/AppLayout.vue'
import LimitBar from '@/components/ui/LimitBar.vue'
import { useCategoriaStore, type Limite } from '@/stores/categoria'

const store      = useCategoriaStore()
const showForm   = ref(false)
const showDelete = ref(false)
const editing    = ref<Limite | null>(null)
const deleting   = ref<Limite | null>(null)

const catOptions = computed(() => [
  { value: '', text: 'Selecione' },
  ...store.categorias.map(c => ({ value: c.id, text: c.nome }))
])

const form = reactive({ titulo: '', categoria_id: '', valor_limite: 0, descricao: '' })

const openCreate = () => {
  editing.value = null
  Object.assign(form, { titulo: '', categoria_id: '', valor_limite: 0, descricao: '' })
  showForm.value = true
}

const openEdit = (l: Limite) => {
  editing.value = l
  Object.assign(form, { titulo: l.titulo, categoria_id: l.categoria_id, valor_limite: Number(l.valor_limite), descricao: l.descricao ?? '' })
  showForm.value = true
}

const confirmDelete = (l: Limite) => { deleting.value = l; showDelete.value = true }

const submit = async () => {
  if (editing.value) {
    await store.updateLimite(editing.value.id, { titulo: form.titulo, valor_limite: form.valor_limite, descricao: form.descricao })
  } else {
    await store.createLimite({ ...form })
  }
  showForm.value = false
}

const doDelete = async () => {
  if (!deleting.value) return
  await store.deleteLimite(deleting.value.id)
  showDelete.value = false
}

onMounted(async () => {
  await store.fetchCategorias()
  await store.fetchLimites()
})
</script>
```

- [ ] **Step 4: Update Profile.vue as navigation hub**

```vue
<!-- resources/js/pages/profile/Profile.vue -->
<template>
  <AppLayout>
    <div class="container-fluid px-3 py-3">
      <!-- User header -->
      <div class="d-flex flex-column align-items-center py-4">
        <div class="profile-avatar mb-2">
          <img v-if="user?.imagem" :src="user.imagem.caminho" :alt="user?.nome" />
          <i v-else class="bi bi-person-circle text-secondary" style="font-size:3.5rem"></i>
        </div>
        <h5 class="mb-0 fw-bold">{{ user?.nome }} {{ user?.sobrenome }}</h5>
        <small class="text-secondary">{{ user?.email }}</small>
      </div>

      <!-- Navigation links -->
      <div class="profile-menu">
        <router-link v-for="item in menuItems" :key="item.to" :to="item.to" class="profile-link">
          <div class="d-flex align-items-center gap-3">
            <div class="link-icon" :style="{ background: item.color + '22', color: item.color }">
              <i :class="item.icon"></i>
            </div>
            <span>{{ item.label }}</span>
          </div>
          <i class="bi bi-chevron-right text-secondary"></i>
        </router-link>
      </div>

      <!-- Dark mode + currency -->
      <div class="card-modern mt-3 p-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <span><i class="bi bi-moon-stars me-2"></i> Modo escuro</span>
          <BFormCheckbox v-model="darkMode" switch @change="uiStore.toggleDarkMode()" />
        </div>
        <div class="d-flex justify-content-between align-items-center">
          <span><i class="bi bi-currency-exchange me-2"></i> Moeda</span>
          <BFormSelect v-model="currency" :options="currencyOpts" style="width:auto" @change="uiStore.setCurrency(currency)" />
        </div>
      </div>

      <!-- Logout -->
      <button class="btn btn-outline-danger w-100 mt-3" @click="handleLogout">
        <i class="bi bi-box-arrow-right me-2"></i> Sair da conta
      </button>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import { useRouter } from 'vue-router'
import AppLayout from '@/components/layouts/AppLayout.vue'
import { useAuthStore } from '@/stores/auth'
import { useUiStore } from '@/stores/ui'

const authStore = useAuthStore()
const uiStore   = useUiStore()
const router    = useRouter()
const user      = computed(() => authStore.user)
const darkMode  = ref(uiStore.darkMode)
const currency  = ref(uiStore.currency ?? 'BRL')

const currencyOpts = [
  { value: 'BRL', text: 'R$ (Real)' },
  { value: 'USD', text: '$ (Dólar)' },
]

const menuItems = [
  { to: '/profile/accounts',      icon: 'bi bi-bank2',          color: '#3b82f6', label: 'Minhas Contas' },
  { to: '/profile/cards',         icon: 'bi bi-credit-card',    color: '#8b5cf6', label: 'Meus Cartões' },
  { to: '/profile/categories',    icon: 'bi bi-tag',            color: '#f97316', label: 'Categorias' },
  { to: '/profile/subcategories', icon: 'bi bi-tags',           color: '#ec4899', label: 'Subcategorias' },
  { to: '/profile/limits',        icon: 'bi bi-exclamation-circle', color: '#ef4444', label: 'Meus Limites' },
]

const handleLogout = async () => {
  await authStore.logout()
  router.push('/login')
}
</script>

<style scoped lang="scss">
.profile-avatar {
  width: 80px; height: 80px; border-radius: 50%; overflow: hidden;
  img { width: 100%; height: 100%; object-fit: cover; }
}

.profile-menu { display: flex; flex-direction: column; gap: 0.25rem; }

.profile-link {
  display: flex; align-items: center; justify-content: space-between;
  padding: 0.875rem 1rem;
  background: var(--bs-card-bg, #fff);
  border: 1px solid var(--bs-border-color);
  border-radius: 0.75rem;
  text-decoration: none;
  color: var(--bs-body-color);
  transition: background .15s;

  &:hover { background: var(--bs-secondary-bg, #f1f5f9); }
}

.link-icon {
  width: 36px; height: 36px; border-radius: 0.5rem;
  display: flex; align-items: center; justify-content: center;
}
</style>
```

- [ ] **Step 5: Update ui store to expose currency + toggleDarkMode**

The existing `stores/ui.ts` must expose `darkMode`, `toggleDarkMode()`, `currency`, and `setCurrency()`. Update it:

```typescript
// resources/js/stores/ui.ts
import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useUiStore = defineStore('ui', () => {
  const darkMode = ref(localStorage.getItem('darkMode') === 'true')
  const currency = ref(localStorage.getItem('currency') ?? 'BRL')

  const toggleDarkMode = () => {
    darkMode.value = !darkMode.value
    localStorage.setItem('darkMode', String(darkMode.value))
  }

  const setCurrency = (c: string) => {
    currency.value = c
    localStorage.setItem('currency', c)
  }

  return { darkMode, currency, toggleDarkMode, setCurrency }
})
```

- [ ] **Step 6: Verify in browser**

```bash
docker compose exec npm npm run dev
```

Navigate `/profile` → links to accounts/cards/categories/limits. Dark mode toggle works. Currency select works.

- [ ] **Step 7: Commit**

```bash
git add resources/js/stores/categoria.ts resources/js/stores/ui.ts resources/js/pages/profile/Categories.vue resources/js/pages/profile/Limits.vue resources/js/pages/profile/Profile.vue
git commit -m "feat: Categoria/Limite stores + Categories, Limits, Profile pages"
```

---

## Phase 4: Dashboard

### Task 13: DashboardService + DashboardController

**Files:**
- Create: `app/Services/DashboardService.php`
- Modify: `app/Http/Controllers/DashboardController.php`

- [ ] **Step 1: Create DashboardService**

```php
<?php
// app/Services/DashboardService.php
namespace App\Services;

use App\Models\User;
use App\Models\Conta;
use App\Models\Cartao;
use App\Models\Lancamento;
use App\Models\Limite;
use App\Domain\Common\TipoLancamento;
use Carbon\Carbon;

class DashboardService
{
    public function getData(User $user): array
    {
        $now   = Carbon::now();
        $mes   = $now->month;
        $ano   = $now->year;
        $today = $now->toDateString();

        $contas  = Conta::with('imagem')
            ->where('user_id', $user->id)
            ->get();

        $cartoes = Cartao::with(['imagem', 'conta'])
            ->where('user_id', $user->id)
            ->get();

        $limites = Limite::with('categoria')
            ->where('user_id', $user->id)
            ->get();

        // Lançamentos do mês atual
        $lancamentosMes = Lancamento::where('user_id', $user->id)
            ->whereMonth('data', $mes)
            ->whereYear('data', $ano)
            ->where('simulado', false)
            ->get();

        $receitas  = $lancamentosMes->where('tipo_lancamento', TipoLancamento::RECEITAS)->sum('valor');
        $despesas  = $lancamentosMes->where('tipo_lancamento', TipoLancamento::DESPESAS)->sum('valor');
        $pago      = $lancamentosMes->where('tipo_lancamento', TipoLancamento::DESPESAS)->where('esta_pago', true)->sum('valor');
        $pendente  = $lancamentosMes->where('tipo_lancamento', TipoLancamento::DESPESAS)->where('esta_pago', false)->sum('valor');

        $saldoTotal    = $contas->where('somar_tela_inicial', true)->sum('saldo');
        $investidoTotal = $contas->sum('saldo_investido');

        return [
            'periodo'   => ['mes' => $mes, 'ano' => $ano],
            'totais'    => [
                'saldo_disponivel' => round((float)$saldoTotal, 2),
                'receitas'         => round((float)$receitas, 2),
                'despesas'         => round((float)$despesas, 2),
                'pago'             => round((float)$pago, 2),
                'pendente'         => round((float)$pendente, 2),
                'saldo_investido'  => round((float)$investidoTotal, 2),
            ],
            'contas'    => $contas->where('somar_tela_inicial', true)->values(),
            'cartoes'   => $cartoes,
            'limites'   => $limites,
        ];
    }
}
```

- [ ] **Step 2: Rewrite DashboardController**

```php
<?php
// app/Http/Controllers/DashboardController.php
namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private DashboardService $service) {}

    public function index(Request $request): JsonResponse
    {
        $data = $this->service->getData($request->user());
        return response()->json(['data' => $data]);
    }
}
```

- [ ] **Step 3: Commit**

```bash
git add app/Services/DashboardService.php app/Http/Controllers/DashboardController.php
git commit -m "feat: DashboardService with monthly totals, contas, cartoes, limites"
```

---

### Task 14: Dashboard.vue — complete

**Files:**
- Modify: `resources/js/pages/home/Dashboard.vue`
- Modify: `resources/js/stores/dashboard.ts`

- [ ] **Step 1: Rewrite dashboard store**

```typescript
// resources/js/stores/dashboard.ts
import { defineStore } from 'pinia'
import { ref } from 'vue'
import axios from 'axios'

export const useDashboardStore = defineStore('dashboard', () => {
  const data    = ref<any>(null)
  const loading = ref(false)

  const fetchDashboard = async () => {
    try {
      loading.value = true
      const res = await axios.get('/api/dashboard')
      data.value = res.data.data
    } finally {
      loading.value = false
    }
  }

  const totais  = () => data.value?.totais   ?? {}
  const contas  = () => data.value?.contas   ?? []
  const cartoes = () => data.value?.cartoes  ?? []
  const limites = () => data.value?.limites  ?? []

  return { data, loading, fetchDashboard, totais, contas, cartoes, limites }
})
```

- [ ] **Step 2: Rewrite Dashboard.vue**

```vue
<!-- resources/js/pages/home/Dashboard.vue -->
<template>
  <AppLayout>
    <div class="container-fluid px-3 py-3">

      <!-- User header -->
      <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="d-flex align-items-center gap-2">
          <div class="avatar">
            <img v-if="user?.imagem" :src="user.imagem.caminho" />
            <i v-else class="bi bi-person-circle fs-3 text-secondary"></i>
          </div>
          <div>
            <div class="fw-semibold">{{ user?.nome }}</div>
            <small class="text-secondary">{{ monthLabel }}</small>
          </div>
        </div>
        <button class="btn btn-sm btn-outline-secondary" @click="authStore.logout().then(() => router.push('/login'))">
          <i class="bi bi-box-arrow-right"></i>
        </button>
      </div>

      <!-- Loading -->
      <div v-if="store.loading" class="text-center py-5">
        <div class="spinner-border text-success"></div>
      </div>

      <template v-else>
        <!-- StatCards grid -->
        <div class="stats-grid mb-4">
          <StatCard label="Saldo Disponível" :value="t.saldo_disponivel" icon="bi bi-wallet2"         icon-bg="#22c55e" color="success" />
          <StatCard label="Receitas"         :value="t.receitas"         icon="bi bi-arrow-down-circle" icon-bg="#22c55e" color="success" />
          <StatCard label="Despesas"         :value="t.despesas"         icon="bi bi-arrow-up-circle"  icon-bg="#ef4444" color="danger"  />
          <StatCard label="Pago"             :value="t.pago"             icon="bi bi-check-circle"     icon-bg="#3b82f6" color="info"    />
          <StatCard label="Pendente"         :value="t.pendente"         icon="bi bi-clock"            icon-bg="#f59e0b" color="warning" />
          <StatCard label="Investido"        :value="t.saldo_investido"  icon="bi bi-graph-up-arrow"   icon-bg="#a855f7" color="primary" />
        </div>

        <!-- Contas -->
        <SectionCard v-if="contas.length" title="Minhas Contas" class="mb-3">
          <template #action>
            <router-link to="/profile/accounts" class="btn btn-sm btn-outline-success">
              <i class="bi bi-plus"></i>
            </router-link>
          </template>
          <div
            v-for="conta in contas"
            :key="conta.id"
            class="conta-item d-flex align-items-center py-2"
          >
            <div class="conta-logo me-3">
              <img v-if="conta.imagem" :src="conta.imagem.caminho" :alt="conta.nome" />
              <i v-else class="bi bi-bank2 text-success"></i>
            </div>
            <div class="flex-grow-1">
              <div class="fw-semibold">{{ conta.nome }}</div>
              <small class="text-secondary">Saldo de</small>
            </div>
            <div class="text-end">
              <div class="fw-bold text-success">{{ fmt(conta.saldo) }}</div>
              <small v-if="Number(conta.saldo_investido) > 0" class="text-secondary">
                Invest: {{ fmt(conta.saldo_investido) }}
              </small>
            </div>
          </div>
        </SectionCard>

        <!-- Cartões -->
        <SectionCard v-if="cartoes.length" title="Meus Cartões" class="mb-3">
          <template #action>
            <router-link to="/profile/cards" class="btn btn-sm btn-outline-success">
              <i class="bi bi-plus"></i>
            </router-link>
          </template>
          <div
            v-for="cartao in cartoes"
            :key="cartao.id"
            class="cartao-item d-flex align-items-center py-2"
          >
            <div class="cartao-logo me-3">
              <img v-if="cartao.imagem" :src="cartao.imagem.caminho" :alt="cartao.nome" />
              <i v-else class="bi bi-credit-card text-success"></i>
            </div>
            <div class="flex-grow-1">
              <div class="fw-semibold">{{ cartao.nome }}</div>
              <small class="text-secondary">Fecha em {{ formatDate(cartao.data_fechamento) }}</small>
            </div>
            <div class="text-end">
              <div class="fw-bold text-danger">{{ fmt(cartao.fatura_total) }}</div>
              <small class="text-secondary">/ {{ fmt(cartao.limite_total) }}</small>
            </div>
          </div>
        </SectionCard>

        <!-- Limites -->
        <SectionCard v-if="limites.length" title="Meus Limites" class="mb-3">
          <LimitBar
            v-for="limite in limites"
            :key="limite.id"
            :name="limite.titulo ?? limite.categoria?.nome"
            :valor-gasto="Number(limite.valor_gasto_atual)"
            :valor-limite="Number(limite.valor_limite)"
            :icon="limite.categoria?.icone"
            :icon-color="limite.categoria?.cor"
            class="mb-2"
          />
        </SectionCard>
      </template>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import AppLayout  from '@/components/layouts/AppLayout.vue'
import StatCard   from '@/components/ui/StatCard.vue'
import SectionCard from '@/components/ui/SectionCard.vue'
import LimitBar   from '@/components/ui/LimitBar.vue'
import { useDashboardStore } from '@/stores/dashboard'
import { useAuthStore } from '@/stores/auth'

const store     = useDashboardStore()
const authStore = useAuthStore()
const router    = useRouter()
const user      = computed(() => authStore.user)
const t         = computed(() => store.totais())
const contas    = computed(() => store.contas())
const cartoes   = computed(() => store.cartoes())
const limites   = computed(() => store.limites())

const monthLabel = computed(() => {
  const now = new Date()
  return now.toLocaleDateString('pt-BR', { month: 'long', year: 'numeric' })
})

const fmt = (v: number) =>
  new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(v ?? 0)

const formatDate = (d: string) =>
  d ? new Date(d).toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit' }) : '—'

onMounted(() => store.fetchDashboard())
</script>

<style scoped lang="scss">
.stats-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 0.75rem;
}

.avatar {
  width: 40px; height: 40px; border-radius: 50%; overflow: hidden;
  img { width: 100%; height: 100%; object-fit: cover; }
}

.conta-logo, .cartao-logo {
  width: 40px; height: 40px; border-radius: 50%; overflow: hidden;
  background: var(--bs-secondary-bg, #f1f5f9);
  display: flex; align-items: center; justify-content: center; font-size: 1.1rem;
  img { width: 100%; height: 100%; object-fit: cover; }
}

.conta-item + .conta-item,
.cartao-item + .cartao-item {
  border-top: 1px solid var(--bs-border-color);
}
</style>
```

- [ ] **Step 3: Verify in browser**

Open `http://localhost` → login → dashboard shows StatCards (initially 0), contas, cartões, limites.  
Create a conta via `/profile/accounts`, return to dashboard — conta appears.

- [ ] **Step 4: Commit**

```bash
git add resources/js/stores/dashboard.ts resources/js/pages/home/Dashboard.vue
git commit -m "feat: complete Dashboard with StatCards, contas, cartoes, limites from real API"
```

---

## Phase 5: Lançamentos

### Task 15: LancamentoRepository + LancamentoService (migrate from Actions)

**Files:**
- Create: `app/Repositories/LancamentoRepository.php`
- Create: `app/Services/LancamentoService.php`

- [ ] **Step 1: Create LancamentoRepository**

```php
<?php
// app/Repositories/LancamentoRepository.php
namespace App\Repositories;

use App\Models\Lancamento;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class LancamentoRepository
{
    public function listByUser(string $userId, array $filters = []): Collection
    {
        $q = Lancamento::with(['contaOrigem', 'contaDestino', 'cartao', 'categoria', 'subcategoria', 'imagem'])
            ->where('user_id', $userId)
            ->where('simulado', false);

        if (!empty($filters['mes'])) {
            $q->whereMonth('data', $filters['mes']);
        }
        if (!empty($filters['ano'])) {
            $q->whereYear('data', $filters['ano']);
        }
        if (!empty($filters['conta_id'])) {
            $q->where(fn($sq) => $sq
                ->where('conta_origem_id', $filters['conta_id'])
                ->orWhere('conta_destino_id', $filters['conta_id'])
            );
        }
        if (!empty($filters['cartao_id'])) {
            $q->where('cartao_id', $filters['cartao_id']);
        }
        if (!empty($filters['tipo'])) {
            $q->where('tipo_lancamento', $filters['tipo']);
        }
        if (isset($filters['saldo_investido']) && $filters['saldo_investido']) {
            $q->where('tipo_lancamento', 'INVESTIMENTOS');
        }

        return $q->orderBy('data', 'desc')->get();
    }

    public function findByUser(string $id, string $userId): ?Lancamento
    {
        return Lancamento::with(['contaOrigem', 'contaDestino', 'cartao', 'categoria', 'subcategoria', 'imagem'])
            ->where('id', $id)
            ->where('user_id', $userId)
            ->first();
    }

    public function create(array $data): Lancamento
    {
        $data['id'] = Str::uuid();
        return Lancamento::create($data);
    }

    public function update(Lancamento $l, array $data): Lancamento
    {
        $l->update($data);
        return $l->fresh(['contaOrigem', 'contaDestino', 'cartao', 'categoria', 'subcategoria', 'imagem']);
    }

    public function softDelete(Lancamento $l): void
    {
        $l->delete();
    }
}
```

- [ ] **Step 2: Create LancamentoService (migrate + fix logic)**

```php
<?php
// app/Services/LancamentoService.php
namespace App\Services;

use App\Domain\Common\TipoLancamento;
use App\Domain\Common\TipoInvestimento;
use App\Models\Conta;
use App\Models\Cartao;
use App\Models\Lancamento;
use App\Models\User;
use App\Repositories\LancamentoRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class LancamentoService
{
    public function __construct(
        private LancamentoRepository $repo,
        private LimiteService $limiteService
    ) {}

    public function list(User $user, array $filters = []): Collection
    {
        return $this->repo->listByUser($user->id, $filters);
    }

    public function create(User $user, array $data): Lancamento
    {
        return DB::transaction(function () use ($user, $data) {
            $data['user_id']   = $user->id;
            $data['esta_pago'] = Carbon::parse($data['data'])->lte(Carbon::today());

            if (isset($data['parcela_total']) && (int)$data['parcela_total'] > 1) {
                return $this->createParcelado($data);
            }

            $lancamento = $this->repo->create($data);

            if ($lancamento->esta_pago) {
                $this->applyMovements($lancamento);
            }

            return $lancamento;
        });
    }

    public function update(User $user, string $id, array $data): Lancamento
    {
        return DB::transaction(function () use ($user, $id, $data) {
            $lancamento = $this->repo->findByUser($id, $user->id);
            abort_unless($lancamento, 404, 'Lançamento não encontrado.');

            // Reverse old movements before updating
            if ($lancamento->esta_pago) {
                $this->reverseMovements($lancamento);
                if ($lancamento->tipo_lancamento === TipoLancamento::DESPESAS && $lancamento->categoria_id) {
                    $this->limiteService->subtractGasto($lancamento->categoria_id, $user->id, (float)$lancamento->valor);
                }
            }

            $data['esta_pago'] = Carbon::parse($data['data'] ?? $lancamento->data)->lte(Carbon::today());
            $updated = $this->repo->update($lancamento, $data);

            if ($updated->esta_pago) {
                $this->applyMovements($updated);
            }

            return $updated;
        });
    }

    public function delete(User $user, string $id): void
    {
        DB::transaction(function () use ($user, $id) {
            $lancamento = $this->repo->findByUser($id, $user->id);
            abort_unless($lancamento, 404, 'Lançamento não encontrado.');

            if ($lancamento->esta_pago) {
                $this->reverseMovements($lancamento);
                if ($lancamento->tipo_lancamento === TipoLancamento::DESPESAS && $lancamento->categoria_id) {
                    $this->limiteService->subtractGasto($lancamento->categoria_id, $user->id, (float)$lancamento->valor);
                }
            }

            $this->repo->softDelete($lancamento);
        });
    }

    // --- Private helpers ---

    private function createParcelado(array $data): Lancamento
    {
        $total       = (int) $data['parcela_total'];
        $valorParcela = round((float)$data['valor'] / $total, 4);
        $dataInicial  = Carbon::parse($data['data']);
        $first        = null;

        for ($i = 1; $i <= $total; $i++) {
            $parcelaData = [...$data,
                'valor'        => $valorParcela,
                'parcela_atual' => $i,
                'data'          => $dataInicial->copy()->addMonths($i - 1)->toDateString(),
            ];
            $parcelaData['esta_pago'] = Carbon::parse($parcelaData['data'])->lte(Carbon::today());

            $lancamento = $this->repo->create($parcelaData);

            if ($lancamento->esta_pago) {
                $this->applyMovements($lancamento);
            }

            if ($i === 1) $first = $lancamento;
        }

        return $first;
    }

    private function applyMovements(Lancamento $l): void
    {
        match ($l->tipo_lancamento) {
            TipoLancamento::RECEITAS      => $this->applyReceita($l),
            TipoLancamento::DESPESAS      => $this->applyDespesa($l),
            TipoLancamento::TRANSFERENCIA => $this->applyTransferencia($l),
            TipoLancamento::INVESTIMENTOS => $this->applyInvestimento($l),
        };

        if ($l->tipo_lancamento === TipoLancamento::DESPESAS && $l->categoria_id) {
            $this->limiteService->addGasto($l->categoria_id, $l->user_id, (float)$l->valor);
        }
    }

    private function reverseMovements(Lancamento $l): void
    {
        match ($l->tipo_lancamento) {
            TipoLancamento::RECEITAS      => $this->reverseReceita($l),
            TipoLancamento::DESPESAS      => $this->reverseDespesa($l),
            TipoLancamento::TRANSFERENCIA => $this->reverseTransferencia($l),
            TipoLancamento::INVESTIMENTOS => $this->reverseInvestimento($l),
        };
    }

    private function applyReceita(Lancamento $l): void
    {
        // Receita sempre vai direto na conta (sem cartão)
        $conta = Conta::find($l->conta_origem_id);
        if ($conta) $conta->increment('saldo', $l->valor);
    }

    private function reverseReceita(Lancamento $l): void
    {
        $conta = Conta::find($l->conta_origem_id);
        if ($conta) $conta->decrement('saldo', $l->valor);
    }

    private function applyDespesa(Lancamento $l): void
    {
        if ($l->cartao_id) {
            $cartao = Cartao::find($l->cartao_id);
            if (!$cartao) return;

            if ($l->tipo_cartao === 'CREDITO') {
                $cartao->increment('fatura_total', $l->valor);
            } else {
                // Débito: debita da conta vinculada ao cartão
                $cartao->conta?->decrement('saldo', $l->valor);
            }
        } else {
            $conta = Conta::find($l->conta_origem_id);
            if ($conta) $conta->decrement('saldo', $l->valor);
        }
    }

    private function reverseDespesa(Lancamento $l): void
    {
        if ($l->cartao_id) {
            $cartao = Cartao::find($l->cartao_id);
            if (!$cartao) return;

            if ($l->tipo_cartao === 'CREDITO') {
                $cartao->decrement('fatura_total', $l->valor);
            } else {
                $cartao->conta?->increment('saldo', $l->valor);
            }
        } else {
            $conta = Conta::find($l->conta_origem_id);
            if ($conta) $conta->increment('saldo', $l->valor);
        }
    }

    private function applyTransferencia(Lancamento $l): void
    {
        $origem = Conta::find($l->conta_origem_id);
        if ($origem) $origem->decrement('saldo', $l->valor);

        if (!empty($l->para_saldo_investido)) {
            if ($origem) $origem->increment('saldo_investido', $l->valor);
        } else {
            $destino = Conta::find($l->conta_destino_id);
            if ($destino) $destino->increment('saldo', $l->valor);
        }
    }

    private function reverseTransferencia(Lancamento $l): void
    {
        $origem = Conta::find($l->conta_origem_id);
        if ($origem) $origem->increment('saldo', $l->valor);

        if (!empty($l->para_saldo_investido)) {
            if ($origem) $origem->decrement('saldo_investido', $l->valor);
        } else {
            $destino = Conta::find($l->conta_destino_id);
            if ($destino) $destino->decrement('saldo', $l->valor);
        }
    }

    private function applyInvestimento(Lancamento $l): void
    {
        $conta = Conta::find($l->conta_origem_id);
        if (!$conta) return;

        // tipo_cartao is repurposed to hold TipoInvestimento in existing schema
        if ($l->tipo_cartao === 'INVESTIR') {
            $conta->decrement('saldo', $l->valor);
            $conta->increment('saldo_investido', $l->valor);
        } else { // RESGATAR
            $conta->decrement('saldo_investido', $l->valor);
            $conta->increment('saldo', $l->valor);
        }
    }

    private function reverseInvestimento(Lancamento $l): void
    {
        $conta = Conta::find($l->conta_origem_id);
        if (!$conta) return;

        if ($l->tipo_cartao === 'INVESTIR') {
            $conta->increment('saldo', $l->valor);
            $conta->decrement('saldo_investido', $l->valor);
        } else {
            $conta->increment('saldo_investido', $l->valor);
            $conta->decrement('saldo', $l->valor);
        }
    }
}
```

> **Note:** The `para_saldo_investido` flag needs a migration to add a boolean column to `lancamentos`. Add it:

```php
// Run this command to create the migration:
// docker compose exec laravel-app php artisan make:migration add_para_saldo_investido_to_lancamentos_table
// Then write:

Schema::table('lancamentos', function (Blueprint $table) {
    $table->boolean('para_saldo_investido')->default(false)->after('conta_destino_id');
});
```

- [ ] **Step 3: Run migration**

```bash
docker compose exec laravel-app php artisan make:migration add_para_saldo_investido_to_lancamentos_table
# Edit the generated file with the schema above, then:
docker compose exec laravel-app php artisan migrate
```

- [ ] **Step 4: Add para_saldo_investido to Lancamento model fillable**

In `app/Models/Lancamento.php` add `'para_saldo_investido'` to `$fillable` and `'para_saldo_investido' => 'boolean'` to `casts()`.

- [ ] **Step 5: Commit**

```bash
git add app/Repositories/LancamentoRepository.php app/Services/LancamentoService.php app/Models/Lancamento.php database/migrations/
git commit -m "feat: LancamentoRepository + LancamentoService with full financial movement logic"
```

---

### Task 16: LancamentoController + Requests + Pest test

**Files:**
- Create: `app/Http/Controllers/LancamentoController.php`
- Create: `app/Http/Requests/StoreLancamentoRequest.php`
- Create: `app/Http/Requests/UpdateLancamentoRequest.php`
- Create: `tests/Feature/LancamentoTest.php`

- [ ] **Step 1: Write failing Pest test**

```php
<?php
// tests/Feature/LancamentoTest.php
use App\Models\User;
use App\Models\Conta;
use App\Models\Lancamento;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can create receita and saldo increases', function () {
    $user  = User::factory()->create();
    $conta = Conta::factory()->create(['user_id' => $user->id, 'saldo' => 0]);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/lancamentos', [
            'titulo'           => 'Salário',
            'valor'            => 3000.00,
            'tipo_lancamento'  => 'RECEITAS',
            'conta_origem_id'  => $conta->id,
            'data'             => now()->toDateString(),
        ])
        ->assertCreated();

    $this->assertDatabaseHas('contas', ['id' => $conta->id, 'saldo' => '3000.0000']);
});

test('user can create despesa and saldo decreases', function () {
    $user  = User::factory()->create();
    $conta = Conta::factory()->create(['user_id' => $user->id, 'saldo' => 1000]);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/lancamentos', [
            'titulo'          => 'Aluguel',
            'valor'           => 500.00,
            'tipo_lancamento' => 'DESPESAS',
            'conta_origem_id' => $conta->id,
            'data'            => now()->toDateString(),
        ])
        ->assertCreated();

    $this->assertDatabaseHas('contas', ['id' => $conta->id, 'saldo' => '500.0000']);
});

test('deleting lancamento reverses saldo', function () {
    $user  = User::factory()->create();
    $conta = Conta::factory()->create(['user_id' => $user->id, 'saldo' => 0]);

    $res = $this->actingAs($user, 'sanctum')
        ->postJson('/api/lancamentos', [
            'titulo'          => 'Salário',
            'valor'           => 1000,
            'tipo_lancamento' => 'RECEITAS',
            'conta_origem_id' => $conta->id,
            'data'            => now()->toDateString(),
        ]);

    $id = $res->json('data.id');

    $this->actingAs($user, 'sanctum')
        ->deleteJson("/api/lancamentos/{$id}")
        ->assertOk();

    $this->assertDatabaseHas('contas', ['id' => $conta->id, 'saldo' => '0.0000']);
});

test('parcelamento creates N lancamentos', function () {
    $user  = User::factory()->create();
    $conta = Conta::factory()->create(['user_id' => $user->id, 'saldo' => 0]);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/lancamentos', [
            'titulo'          => 'Compra parcelada',
            'valor'           => 300,
            'tipo_lancamento' => 'DESPESAS',
            'conta_origem_id' => $conta->id,
            'parcela_total'   => 3,
            'data'            => now()->toDateString(),
        ])
        ->assertCreated();

    $this->assertDatabaseCount('lancamentos', 3);
});

test('user can list lancamentos filtered by mes/ano', function () {
    $user  = User::factory()->create();
    $conta = Conta::factory()->create(['user_id' => $user->id]);

    Lancamento::factory()->create(['user_id' => $user->id, 'conta_origem_id' => $conta->id, 'data' => now()]);
    Lancamento::factory()->create(['user_id' => $user->id, 'conta_origem_id' => $conta->id, 'data' => now()->subYear()]);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/lancamentos?mes=' . now()->month . '&ano=' . now()->year)
        ->assertOk()
        ->assertJsonCount(1, 'data');
});
```

- [ ] **Step 2: Create LancamentoFactory**

```php
<?php
// database/factories/LancamentoFactory.php
namespace Database\Factories;

use App\Models\User;
use App\Models\Conta;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class LancamentoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id'              => Str::uuid(),
            'user_id'         => User::factory(),
            'titulo'          => $this->faker->word(),
            'descricao'       => null,
            'valor'           => $this->faker->randomFloat(2, 10, 500),
            'tipo_lancamento' => 'RECEITAS',
            'conta_origem_id' => Conta::factory(),
            'data'            => now()->toDateString(),
            'esta_pago'       => true,
            'simulado'        => false,
            'parcela_total'   => null,
            'parcela_atual'   => null,
            'para_saldo_investido' => false,
        ];
    }
}
```

Also add `use HasFactory;` to `app/Models/Lancamento.php`.

- [ ] **Step 3: Run test — expect FAIL**

```bash
docker compose exec laravel-app php artisan test tests/Feature/LancamentoTest.php
```

- [ ] **Step 4: Create Form Requests**

```php
<?php
// app/Http/Requests/StoreLancamentoRequest.php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLancamentoRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'titulo'               => 'required|string|max:150',
            'descricao'            => 'nullable|string|max:500',
            'valor'                => 'required|numeric|min:0.01',
            'tipo_lancamento'      => 'required|in:RECEITAS,DESPESAS,TRANSFERENCIA,INVESTIMENTOS',
            'conta_origem_id'      => 'required|exists:contas,id',
            'conta_destino_id'     => 'nullable|exists:contas,id',
            'cartao_id'            => 'nullable|exists:cartoes,id',
            'tipo_cartao'          => 'nullable|in:CREDITO,DEBITO,INVESTIR,RESGATAR',
            'categoria_id'         => 'nullable|exists:categorias,id',
            'subcategoria_id'      => 'nullable|exists:subcategorias,id',
            'data'                 => 'required|date',
            'parcela_total'        => 'nullable|integer|min:1|max:120',
            'para_saldo_investido' => 'sometimes|boolean',
            'imagem_id'            => 'nullable|exists:imagens,id',
        ];
    }
}
```

```php
<?php
// app/Http/Requests/UpdateLancamentoRequest.php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLancamentoRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'conta_origem_id'  => 'sometimes|exists:contas,id',
            'conta_destino_id' => 'nullable|exists:contas,id',
            'valor'            => 'sometimes|numeric|min:0.01',
            'data'             => 'sometimes|date',
            'descricao'        => 'nullable|string|max:500',
            'categoria_id'     => 'nullable|exists:categorias,id',
            'subcategoria_id'  => 'nullable|exists:subcategorias,id',
        ];
    }
}
```

- [ ] **Step 5: Create LancamentoController**

```php
<?php
// app/Http/Controllers/LancamentoController.php
namespace App\Http\Controllers;

use App\Http\Requests\StoreLancamentoRequest;
use App\Http\Requests\UpdateLancamentoRequest;
use App\Services\LancamentoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LancamentoController extends Controller
{
    public function __construct(private LancamentoService $service) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['mes', 'ano', 'conta_id', 'cartao_id', 'tipo', 'saldo_investido']);
        return response()->json(['data' => $this->service->list($request->user(), $filters)]);
    }

    public function store(StoreLancamentoRequest $request): JsonResponse
    {
        $l = $this->service->create($request->user(), $request->validated());
        return response()->json(['data' => $l, 'message' => 'Lançamento criado.'], 201);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $lancamentos = $this->service->list($request->user(), []);
        $l = $lancamentos->firstWhere('id', $id);
        abort_unless($l, 404);
        return response()->json(['data' => $l]);
    }

    public function update(UpdateLancamentoRequest $request, string $id): JsonResponse
    {
        $l = $this->service->update($request->user(), $id, $request->validated());
        return response()->json(['data' => $l, 'message' => 'Lançamento atualizado.']);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $this->service->delete($request->user(), $id);
        return response()->json(['message' => 'Lançamento excluído.']);
    }
}
```

- [ ] **Step 6: Run test — expect PASS**

```bash
docker compose exec laravel-app php artisan test tests/Feature/LancamentoTest.php
```

Expected: 5 passed.

- [ ] **Step 7: Run all tests**

```bash
docker compose exec laravel-app php artisan test
```

Expected: all tests pass (AuthTest + ContaTest + CartaoTest + LancamentoTest).

- [ ] **Step 8: Commit**

```bash
git add app/Http/Controllers/LancamentoController.php app/Http/Requests/StoreLancamentoRequest.php app/Http/Requests/UpdateLancamentoRequest.php database/factories/LancamentoFactory.php app/Models/Lancamento.php tests/Feature/LancamentoTest.php
git commit -m "feat: LancamentoController + all Pest tests passing"
```

---

### Task 17: Frontend — TransactionModal + Transactions.vue + transaction store

**Files:**
- Modify: `resources/js/components/modals/TransactionModal.vue`
- Modify: `resources/js/pages/transactions/Transactions.vue`
- Modify: `resources/js/stores/transaction.ts`

- [ ] **Step 1: Rewrite transaction store**

```typescript
// resources/js/stores/transaction.ts
import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from 'axios'

export interface Lancamento {
  id: string
  titulo: string
  descricao?: string
  valor: number
  tipo_lancamento: 'RECEITAS' | 'DESPESAS' | 'TRANSFERENCIA' | 'INVESTIMENTOS'
  conta_origem_id?: string
  conta_destino_id?: string
  cartao_id?: string
  tipo_cartao?: string
  categoria_id?: string
  subcategoria_id?: string
  data: string
  esta_pago: boolean
  parcela_total?: number
  parcela_atual?: number
  para_saldo_investido?: boolean
  categoria?: { id: string; nome: string; cor: string; icone: string }
  subcategoria?: { id: string; nome: string }
  conta_origem?: { id: string; nome: string }
  conta_destino?: { id: string; nome: string }
  cartao?: { id: string; nome: string }
}

export const useTransactionStore = defineStore('transaction', () => {
  const lancamentos = ref<Lancamento[]>([])
  const loading     = ref(false)
  const mesSelecionado = ref(new Date().getMonth() + 1)
  const anoSelecionado = ref(new Date().getFullYear())
  const filtroTipo     = ref<string>('')
  const filtroContaId  = ref<string>('')
  const filtroCartaoId = ref<string>('')

  const fetchLancamentos = async () => {
    try {
      loading.value = true
      const params: Record<string, any> = {
        mes: mesSelecionado.value,
        ano: anoSelecionado.value,
      }
      if (filtroTipo.value)     params.tipo     = filtroTipo.value
      if (filtroContaId.value)  params.conta_id = filtroContaId.value
      if (filtroCartaoId.value) params.cartao_id = filtroCartaoId.value

      const { data } = await axios.get('/api/lancamentos', { params })
      lancamentos.value = data.data
    } finally {
      loading.value = false
    }
  }

  const createLancamento = async (payload: object) => {
    const { data } = await axios.post('/api/lancamentos', payload)
    await fetchLancamentos()
    return data.data
  }

  const deleteLancamento = async (id: string) => {
    await axios.delete(`/api/lancamentos/${id}`)
    await fetchLancamentos()
  }

  const totalGastos = computed(() =>
    lancamentos.value
      .filter(l => l.tipo_lancamento === 'DESPESAS')
      .reduce((s, l) => s + Number(l.valor), 0)
  )

  const totalReceitas = computed(() =>
    lancamentos.value
      .filter(l => l.tipo_lancamento === 'RECEITAS')
      .reduce((s, l) => s + Number(l.valor), 0)
  )

  const balanco = computed(() => totalReceitas.value - totalGastos.value)

  return {
    lancamentos, loading,
    mesSelecionado, anoSelecionado,
    filtroTipo, filtroContaId, filtroCartaoId,
    fetchLancamentos, createLancamento, deleteLancamento,
    totalGastos, totalReceitas, balanco,
  }
})
```

- [ ] **Step 2: Rewrite TransactionModal.vue**

```vue
<!-- resources/js/components/modals/TransactionModal.vue -->
<template>
  <!-- Type selection sheet -->
  <div class="modal-overlay" @click.self="emit('close')">
    <div class="type-sheet" v-if="!tipo">
      <div class="sheet-handle"></div>
      <h6 class="text-center mb-4">O que você quer <strong>Adicionar?</strong></h6>
      <button class="type-btn income"    @click="tipo = 'RECEITAS'">
        Receita <i class="bi bi-plus-circle ms-auto"></i>
      </button>
      <button class="type-btn expense"   @click="tipo = 'DESPESAS'">
        Despesa <i class="bi bi-dash-circle ms-auto"></i>
      </button>
      <button class="type-btn transfer"  @click="tipo = 'TRANSFERENCIA'">
        Transferência <i class="bi bi-arrow-left-right ms-auto"></i>
      </button>
      <button class="type-btn invest"    @click="tipo = 'INVESTIMENTOS'">
        Investimento <i class="bi bi-graph-up-arrow ms-auto"></i>
      </button>
    </div>

    <!-- Form sheet -->
    <div class="form-sheet" v-else>
      <div class="sheet-handle"></div>
      <div class="d-flex align-items-center mb-3">
        <button class="btn btn-sm btn-ghost me-2" @click="tipo = ''">
          <i class="bi bi-arrow-left"></i>
        </button>
        <h6 class="mb-0">Nova {{ tipoLabel }}</h6>
      </div>

      <BForm @submit.prevent="submit">
        <!-- Título -->
        <div class="mb-3">
          <label class="form-label">Título *</label>
          <BFormInput v-model="form.titulo" required placeholder="Ex: Aluguel" />
        </div>

        <!-- Valor -->
        <div class="mb-3">
          <label class="form-label">Valor *</label>
          <div class="input-group">
            <span class="input-group-text">R$</span>
            <BFormInput v-model.number="form.valor" type="number" step="0.01" min="0.01" required />
          </div>
        </div>

        <!-- Data -->
        <div class="mb-3">
          <label class="form-label">Data *</label>
          <BFormInput v-model="form.data" type="date" required />
        </div>

        <!-- Conta origem -->
        <div class="mb-3">
          <label class="form-label">{{ tipo === 'TRANSFERENCIA' ? 'Conta de origem *' : 'Conta *' }}</label>
          <BFormSelect v-model="form.conta_origem_id" :options="contaOptions" required />
        </div>

        <!-- Conta destino (transferência) -->
        <template v-if="tipo === 'TRANSFERENCIA'">
          <div class="mb-3">
            <label class="form-label">Conta de destino</label>
            <BFormSelect v-model="form.conta_destino_id" :options="[{ value: '', text: '— Saldo Investido —' }, ...contaOptions.slice(1)]" />
          </div>
          <div class="mb-3">
            <BFormCheckbox v-model="form.para_saldo_investido" switch>
              Enviar para saldo investido
            </BFormCheckbox>
          </div>
        </template>

        <!-- Cartão (despesa) -->
        <template v-if="tipo === 'DESPESAS'">
          <div class="mb-3">
            <label class="form-label">Cartão (opcional)</label>
            <BFormSelect v-model="form.cartao_id" :options="cartaoOptions" />
          </div>
          <div v-if="form.cartao_id" class="mb-3">
            <label class="form-label">Tipo de pagamento</label>
            <BFormSelect v-model="form.tipo_cartao" :options="[{ value: 'CREDITO', text: 'Crédito' }, { value: 'DEBITO', text: 'Débito' }]" />
          </div>
          <div v-if="form.cartao_id && form.tipo_cartao === 'CREDITO'" class="mb-3">
            <label class="form-label">Parcelamento</label>
            <BFormSelect v-model.number="form.parcela_total" :options="parcelaOptions" />
          </div>
        </template>

        <!-- Tipo investimento -->
        <template v-if="tipo === 'INVESTIMENTOS'">
          <div class="mb-3">
            <label class="form-label">Operação *</label>
            <BFormSelect v-model="form.tipo_cartao" :options="[{ value: 'INVESTIR', text: 'Investir' }, { value: 'RESGATAR', text: 'Resgatar' }]" required />
          </div>
        </template>

        <!-- Categoria -->
        <div class="mb-3" v-if="tipo !== 'TRANSFERENCIA'">
          <label class="form-label">Categoria</label>
          <BFormSelect v-model="form.categoria_id" :options="categoriaOptions" />
        </div>

        <!-- Descrição -->
        <div class="mb-3">
          <label class="form-label">Descrição</label>
          <BFormInput v-model="form.descricao" placeholder="Observação opcional" />
        </div>

        <div v-if="error" class="alert alert-danger py-2 mb-3">{{ error }}</div>

        <div class="d-flex gap-2">
          <BButton variant="outline-secondary" class="flex-fill" @click="emit('close')">Cancelar</BButton>
          <BButton variant="success" class="flex-fill" type="submit" :disabled="saving">
            <span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>
            Salvar
          </BButton>
        </div>
      </BForm>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue'
import { useContaStore } from '@/stores/conta'
import { useCartaoStore } from '@/stores/cartao'
import { useCategoriaStore } from '@/stores/categoria'
import { useTransactionStore } from '@/stores/transaction'

const emit = defineEmits<{ (e: 'close'): void; (e: 'created'): void }>()

const contaStore     = useContaStore()
const cartaoStore    = useCartaoStore()
const categoriaStore = useCategoriaStore()
const txStore        = useTransactionStore()

const tipo   = ref('')
const saving = ref(false)
const error  = ref('')

const tipoLabel = computed(() => ({
  RECEITAS: 'Receita', DESPESAS: 'Despesa',
  TRANSFERENCIA: 'Transferência', INVESTIMENTOS: 'Investimento'
}[tipo.value] ?? ''))

const form = reactive({
  titulo: '', valor: 0, data: new Date().toISOString().substring(0, 10),
  conta_origem_id: '', conta_destino_id: '', cartao_id: '', tipo_cartao: 'CREDITO',
  categoria_id: '', descricao: '', parcela_total: 1, para_saldo_investido: false,
})

const contaOptions = computed(() => [
  { value: '', text: 'Selecione a conta' },
  ...contaStore.contas.map(c => ({ value: c.id, text: c.nome }))
])

const cartaoOptions = computed(() => [
  { value: '', text: 'Sem cartão' },
  ...cartaoStore.cartoes.map(c => ({ value: c.id, text: c.nome }))
])

const categoriaOptions = computed(() => [
  { value: '', text: 'Sem categoria' },
  ...categoriaStore.categorias.filter(c => c.ativo).map(c => ({ value: c.id, text: c.nome }))
])

const parcelaOptions = [
  { value: 1, text: 'À vista' },
  ...[2,3,4,5,6,7,8,9,10,11,12,18,24,36,48,60].map(n => ({ value: n, text: `${n}x` }))
]

const submit = async () => {
  try {
    saving.value = true
    error.value  = ''

    const payload: Record<string, any> = {
      titulo:          form.titulo,
      valor:           form.valor,
      data:            form.data,
      tipo_lancamento: tipo.value,
      conta_origem_id: form.conta_origem_id,
      descricao:       form.descricao || undefined,
      categoria_id:    form.categoria_id || undefined,
    }

    if (tipo.value === 'DESPESAS') {
      if (form.cartao_id) {
        payload.cartao_id   = form.cartao_id
        payload.tipo_cartao = form.tipo_cartao
        if (form.tipo_cartao === 'CREDITO' && form.parcela_total > 1) {
          payload.parcela_total = form.parcela_total
        }
      }
    }

    if (tipo.value === 'TRANSFERENCIA') {
      payload.para_saldo_investido = form.para_saldo_investido
      if (!form.para_saldo_investido) {
        payload.conta_destino_id = form.conta_destino_id
      }
    }

    if (tipo.value === 'INVESTIMENTOS') {
      payload.tipo_cartao = form.tipo_cartao // INVESTIR or RESGATAR
    }

    await txStore.createLancamento(payload)
    emit('created')
    emit('close')
  } catch (e: any) {
    error.value = e.response?.data?.message ?? 'Erro ao salvar'
  } finally {
    saving.value = false
  }
}

onMounted(async () => {
  await Promise.all([
    contaStore.fetchContas(),
    cartaoStore.fetchCartoes(),
    categoriaStore.fetchCategorias(),
  ])
})
</script>

<style scoped lang="scss">
.modal-overlay {
  position: fixed; inset: 0;
  background: rgba(0,0,0,.5);
  display: flex; align-items: flex-end;
  z-index: 2000;
}

.type-sheet, .form-sheet {
  background: var(--bs-card-bg, #fff);
  border-radius: 1.25rem 1.25rem 0 0;
  padding: 1.25rem;
  width: 100%;
  max-height: 90vh;
  overflow-y: auto;
}

.sheet-handle {
  width: 40px; height: 4px;
  background: var(--bs-border-color);
  border-radius: 2px;
  margin: 0 auto 1rem;
}

.type-btn {
  display: flex; align-items: center;
  width: 100%; padding: 1rem 1.25rem;
  border: 2px solid var(--bs-border-color);
  border-radius: 0.75rem;
  background: none;
  font-weight: 500;
  margin-bottom: 0.75rem;
  font-size: 1rem;
  color: var(--bs-body-color);
  transition: border-color .15s;

  &.income  { border-color: #22c55e; color: #22c55e; }
  &.expense { border-color: #ef4444; color: #ef4444; }
  &.transfer { border-color: #3b82f6; color: #3b82f6; }
  &.invest  { border-color: #a855f7; color: #a855f7; }
}
</style>
```

- [ ] **Step 3: Rewrite Transactions.vue**

```vue
<!-- resources/js/pages/transactions/Transactions.vue -->
<template>
  <AppLayout>
    <div class="tx-page">
      <!-- Header -->
      <div class="tx-header px-3 pt-3 pb-0">
        <div class="d-flex align-items-center mb-2">
          <div>
            <small class="text-secondary">Transações</small>
            <h5 class="mb-0 fw-bold">Todos os lançamentos</h5>
          </div>
        </div>

        <!-- Month carousel -->
        <div class="month-carousel d-flex align-items-center gap-2 mb-2 overflow-auto pb-1">
          <button class="btn btn-sm btn-ghost" @click="prevMonth"><i class="bi bi-chevron-left"></i></button>
          <div
            v-for="m in visibleMonths"
            :key="m.key"
            class="month-chip"
            :class="{ active: m.mes === store.mesSelecionado && m.ano === store.anoSelecionado }"
            @click="selectMonth(m.mes, m.ano)"
          >
            <div class="month-name">{{ m.label }}</div>
            <div class="month-year">{{ m.ano }}</div>
          </div>
          <button class="btn btn-sm btn-ghost" @click="nextMonth"><i class="bi bi-chevron-right"></i></button>
        </div>

        <!-- Filter chips -->
        <div class="d-flex gap-2 overflow-auto pb-2">
          <button
            v-for="chip in chips"
            :key="chip.value"
            class="filter-chip"
            :class="{ active: activeChip === chip.value }"
            @click="setChip(chip.value)"
          >{{ chip.label }}</button>
        </div>
      </div>

      <!-- List -->
      <div class="tx-list px-3 py-2" v-if="!store.loading">
        <template v-for="(group, date) in groupedByDate" :key="date">
          <div class="date-header">{{ formatDate(date) }}</div>
          <div
            v-for="l in group"
            :key="l.id"
            class="tx-item card-modern mb-2"
          >
            <div class="d-flex align-items-center">
              <div class="tx-icon me-3" :class="tipoClass(l)">
                <i :class="l.categoria?.icone || tipoIcon(l)"></i>
              </div>
              <div class="flex-grow-1">
                <div class="fw-semibold">{{ l.titulo }}</div>
                <small class="text-secondary">
                  {{ l.conta_origem?.nome ?? l.cartao?.nome ?? '—' }}
                  <span v-if="l.parcela_total">
                    · <span class="badge bg-secondary">Parcelado {{ l.parcela_atual }}/{{ l.parcela_total }}</span>
                  </span>
                </small>
              </div>
              <div class="text-end">
                <div class="fw-bold" :class="tipoAmountClass(l)">
                  {{ tipoSign(l) }}{{ fmt(l.valor) }}
                </div>
                <button class="btn btn-xs btn-ghost" @click="confirmDelete(l)">
                  <i class="bi bi-trash text-danger" style="font-size:.75rem"></i>
                </button>
              </div>
            </div>
          </div>
          <p v-if="!store.lancamentos.length" class="text-center text-secondary py-5">
            Nenhum lançamento neste período.
          </p>
        </template>
      </div>

      <div v-else class="text-center py-5"><div class="spinner-border text-success"></div></div>

      <!-- Sticky footer -->
      <div class="tx-footer">
        <div class="d-flex justify-content-between mb-1">
          <span class="text-secondary">Gastos</span>
          <span class="text-danger fw-semibold">{{ fmt(store.totalGastos) }}</span>
        </div>
        <div class="d-flex justify-content-between mb-1">
          <span class="text-secondary">Receitas</span>
          <span class="text-success fw-semibold">{{ fmt(store.totalReceitas) }}</span>
        </div>
        <div class="d-flex justify-content-between fw-bold">
          <span>Balanço total</span>
          <span :class="store.balanco >= 0 ? 'text-success' : 'text-danger'">
            {{ fmt(store.balanco) }}
          </span>
        </div>
      </div>
    </div>

    <BModal v-model="showDelete" title="Excluir lançamento" @ok="doDelete">
      <p>Excluir <strong>{{ deleting?.titulo }}</strong>? O saldo será revertido.</p>
    </BModal>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import AppLayout from '@/components/layouts/AppLayout.vue'
import { useTransactionStore, type Lancamento } from '@/stores/transaction'

const store = useTransactionStore()
const showDelete = ref(false)
const deleting   = ref<Lancamento | null>(null)
const activeChip = ref('geral')

const chips = [
  { value: 'geral',   label: 'Geral' },
  { value: 'cartoes', label: 'Cartões' },
  { value: 'conta',   label: 'Conta' },
]

const setChip = (v: string) => {
  activeChip.value = v
  store.filtroTipo     = ''
  store.filtroContaId  = ''
  store.filtroCartaoId = ''
  store.fetchLancamentos()
}

// Month carousel — 3 months visible
const visibleMonths = computed(() => {
  const result = []
  const meses = ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez']
  for (let i = -1; i <= 1; i++) {
    const d = new Date(store.anoSelecionado, store.mesSelecionado - 1 + i)
    result.push({ mes: d.getMonth() + 1, ano: d.getFullYear(), label: meses[d.getMonth()], key: `${d.getFullYear()}-${d.getMonth()}` })
  }
  return result
})

const prevMonth = () => {
  if (store.mesSelecionado === 1) { store.mesSelecionado = 12; store.anoSelecionado-- }
  else store.mesSelecionado--
  store.fetchLancamentos()
}

const nextMonth = () => {
  if (store.mesSelecionado === 12) { store.mesSelecionado = 1; store.anoSelecionado++ }
  else store.mesSelecionado++
  store.fetchLancamentos()
}

const selectMonth = (mes: number, ano: number) => {
  store.mesSelecionado = mes
  store.anoSelecionado = ano
  store.fetchLancamentos()
}

const groupedByDate = computed(() => {
  const groups: Record<string, Lancamento[]> = {}
  for (const l of store.lancamentos) {
    const d = l.data.substring(0, 10)
    if (!groups[d]) groups[d] = []
    groups[d].push(l)
  }
  return groups
})

const fmt = (v: number) =>
  new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(v ?? 0)

const formatDate = (d: string) =>
  new Date(d + 'T12:00:00').toLocaleDateString('pt-BR', { weekday: 'short', day: '2-digit', month: 'long' })

const tipoIcon = (l: Lancamento) => ({
  RECEITAS: 'bi bi-arrow-down-circle text-success',
  DESPESAS: 'bi bi-arrow-up-circle text-danger',
  TRANSFERENCIA: 'bi bi-arrow-left-right text-info',
  INVESTIMENTOS: 'bi bi-graph-up-arrow text-purple',
}[l.tipo_lancamento] ?? 'bi bi-dash-circle')

const tipoClass = (l: Lancamento) => ({
  RECEITAS:      'tx-icon-income',
  DESPESAS:      'tx-icon-expense',
  TRANSFERENCIA: 'tx-icon-transfer',
  INVESTIMENTOS: 'tx-icon-invest',
}[l.tipo_lancamento] ?? '')

const tipoAmountClass = (l: Lancamento) => ({
  RECEITAS: 'text-success', DESPESAS: 'text-danger',
  TRANSFERENCIA: 'text-info', INVESTIMENTOS: 'text-purple',
}[l.tipo_lancamento] ?? '')

const tipoSign = (l: Lancamento) =>
  ['RECEITAS'].includes(l.tipo_lancamento) ? '+' : '-'

const confirmDelete = (l: Lancamento) => { deleting.value = l; showDelete.value = true }
const doDelete = async () => {
  if (!deleting.value) return
  await store.deleteLancamento(deleting.value.id)
  showDelete.value = false
}

onMounted(() => store.fetchLancamentos())
</script>

<style scoped lang="scss">
.tx-page { display: flex; flex-direction: column; height: 100%; }

.month-carousel { scrollbar-width: none; &::-webkit-scrollbar { display: none; } }

.month-chip {
  flex-shrink: 0; text-align: center; padding: 0.4rem 0.75rem;
  border-radius: 0.75rem; cursor: pointer;
  color: var(--bs-secondary); background: none;
  transition: all .15s;
  .month-name { font-size: .85rem; font-weight: 500; }
  .month-year { font-size: .65rem; }
  &.active { background: #22c55e; color: white; }
}

.filter-chip {
  flex-shrink: 0; padding: 0.3rem 0.875rem;
  border-radius: 999px; border: 1px solid var(--bs-border-color);
  background: none; font-size: .85rem; color: var(--bs-body-color);
  &.active { background: #22c55e; color: white; border-color: #22c55e; }
}

.date-header {
  font-size: .75rem; color: var(--bs-secondary);
  font-weight: 600; margin: 0.75rem 0 0.35rem;
  text-transform: capitalize;
}

.tx-item { padding: 0.75rem 1rem; }

.tx-icon {
  width: 40px; height: 40px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center; font-size: 1.1rem;
  flex-shrink: 0;

  &.tx-icon-income   { background: rgba(34,197,94,.15);  color: #22c55e; }
  &.tx-icon-expense  { background: rgba(239,68,68,.15);  color: #ef4444; }
  &.tx-icon-transfer { background: rgba(59,130,246,.15); color: #3b82f6; }
  &.tx-icon-invest   { background: rgba(168,85,247,.15); color: #a855f7; }
}

.tx-footer {
  position: sticky; bottom: var(--bottom-nav-height, 70px);
  background: var(--bs-card-bg, #fff);
  border-top: 1px solid var(--bs-border-color);
  padding: 0.875rem 1.25rem;
  z-index: 100;
}

@media (min-width: 768px) {
  .tx-footer { bottom: 0; }
}

.btn-xs { padding: 0.1rem 0.25rem; }
.text-purple { color: #a855f7; }
</style>
```

- [ ] **Step 4: Verify in browser**

Navigate to `/transactions`. Verify:
- Month carousel switches months
- `+` FAB opens TransactionModal
- Create a Receita → appears in list, balance updates
- Create a Despesa → appears in list with negative sign
- Delete a lançamento → list updates, balance updates
- Footer shows gastos / receitas / balanço

- [ ] **Step 5: Commit**

```bash
git add resources/js/stores/transaction.ts resources/js/components/modals/TransactionModal.vue resources/js/pages/transactions/Transactions.vue
git commit -m "feat: complete TransactionModal (all types), Transactions.vue with month carousel, filters, sticky footer"
```

---

**End of Phases 3–6.**  
Continue with: `docs/superpowers/plans/2026-05-16-financas-pessoais-phase7-9.md`
