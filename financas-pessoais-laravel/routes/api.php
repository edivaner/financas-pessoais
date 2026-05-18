<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ContaController;
use App\Http\Controllers\CartaoController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\SubcategoriaController;
use App\Http\Controllers\LimiteController;
use App\Http\Controllers\LancamentoController;
use App\Http\Controllers\ImagemController;
use App\Http\Controllers\ResetController;

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);
    Route::post('/logout',   [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::get('/user',      [AuthController::class, 'user'])->middleware('auth:sanctum');
    Route::put('/user',        [AuthController::class, 'updateUser'])->middleware('auth:sanctum');
    Route::put('/preferences', [AuthController::class, 'updatePreferences'])->middleware('auth:sanctum');
    Route::post('/avatar',   [AuthController::class, 'avatar'])->middleware('auth:sanctum');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::apiResource('contas',        ContaController::class);
    Route::apiResource('cartoes',       CartaoController::class);
    Route::apiResource('categorias',    CategoriaController::class);
    Route::apiResource('subcategorias', SubcategoriaController::class);
    Route::apiResource('limites',       LimiteController::class);
    Route::apiResource('lancamentos',   LancamentoController::class);
    Route::delete('account/reset',     ResetController::class);
    Route::get('imagens/bank-logos',   [ImagemController::class, 'bankLogos']);
    Route::post('imagens',             [ImagemController::class, 'store']);
});
