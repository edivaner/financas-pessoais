<?php
namespace App\Http\Controllers;

use App\Http\Requests\StoreContaRequest;
use App\Http\Requests\UpdateContaRequest;
use App\Services\ContaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContaController extends Controller
{
    public function __construct(private ContaService $service) {}

    public function index(Request $request): JsonResponse
    {
        $contas = $this->service->list($request->user());
        return response()->json(['data' => $contas]);
    }

    public function store(StoreContaRequest $request): JsonResponse
    {
        $conta = $this->service->create($request->user(), $request->validated());
        return response()->json(['data' => $conta, 'message' => 'Conta criada.'], 201);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $conta = $this->service->find($request->user(), $id);
        return response()->json(['data' => $conta]);
    }

    public function update(UpdateContaRequest $request, string $id): JsonResponse
    {
        $conta = $this->service->update($request->user(), $id, $request->validated());
        return response()->json(['data' => $conta, 'message' => 'Conta atualizada.']);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $this->service->delete($request->user(), $id);
        return response()->json(['message' => 'Conta excluída.']);
    }
}
