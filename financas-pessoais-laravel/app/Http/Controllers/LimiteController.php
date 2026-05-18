<?php
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
