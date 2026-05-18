<?php
namespace App\Http\Controllers;

use App\Http\Requests\StoreCartaoRequest;
use App\Http\Requests\UpdateCartaoRequest;
use App\Services\CartaoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartaoController extends Controller
{
    public function __construct(private CartaoService $service) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->service->list($request->user())]);
    }

    public function store(StoreCartaoRequest $request): JsonResponse
    {
        $cartao = $this->service->create($request->user(), $request->validated());
        return response()->json(['data' => $cartao, 'message' => 'Cartão criado.'], 201);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        return response()->json(['data' => $this->service->find($request->user(), $id)]);
    }

    public function update(UpdateCartaoRequest $request, string $id): JsonResponse
    {
        $cartao = $this->service->update($request->user(), $id, $request->validated());
        return response()->json(['data' => $cartao, 'message' => 'Cartão atualizado.']);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $this->service->delete($request->user(), $id);
        return response()->json(['message' => 'Cartão excluído.']);
    }
}
