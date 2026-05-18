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
