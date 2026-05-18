<?php
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
