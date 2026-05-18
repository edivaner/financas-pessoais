<?php
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
