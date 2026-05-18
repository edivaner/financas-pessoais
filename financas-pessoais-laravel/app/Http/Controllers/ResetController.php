<?php
namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Lancamento;
use App\Models\Limite;
use App\Models\Cartao;
use App\Models\Conta;
use App\Models\Subcategoria;
use App\Models\Categoria;

class ResetController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        Lancamento::where('user_id', $userId)->forceDelete();
        Limite::where('user_id', $userId)->delete();
        Cartao::where('user_id', $userId)->forceDelete();
        Conta::where('user_id', $userId)->forceDelete();
        Subcategoria::where('user_id', $userId)->delete();
        Categoria::where('user_id', $userId)->delete();

        return response()->json(['message' => 'Conta zerada com sucesso.']);
    }
}
