<?php
// app/Http/Controllers/ImagemController.php
namespace App\Http\Controllers;

use App\Services\ImagemService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ImagemController extends Controller
{
    public function __construct(private ImagemService $service) {}

    public function bankLogos(): JsonResponse
    {
        $banks = [
            ['slug' => 'nubank',        'nome' => 'Nubank',           'path' => '/images/banks/nubank.svg'],
            ['slug' => 'inter',         'nome' => 'Banco Inter',      'path' => '/images/banks/inter.svg'],
            ['slug' => 'itau',          'nome' => 'Itaú',             'path' => '/images/banks/itau.svg'],
            ['slug' => 'bradesco',      'nome' => 'Bradesco',         'path' => '/images/banks/bradesco.svg'],
            ['slug' => 'santander',     'nome' => 'Santander',        'path' => '/images/banks/santander.svg'],
            ['slug' => 'bancodobrasil', 'nome' => 'Banco do Brasil',  'path' => '/images/banks/bancodobrasil.svg'],
            ['slug' => 'caixa',         'nome' => 'Caixa',            'path' => '/images/banks/caixa.svg'],
            ['slug' => 'btg',           'nome' => 'BTG Pactual',      'path' => '/images/banks/btg.svg'],
            ['slug' => 'xp',            'nome' => 'XP Investimentos', 'path' => '/images/banks/xp.svg'],
            ['slug' => 'safra',         'nome' => 'Safra',            'path' => '/images/banks/safra.svg'],
            ['slug' => 'carteira',      'nome' => 'Carteira',         'path' => '/images/banks/carteira.svg'],
        ];
        return response()->json(['data' => $banks]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'imagem' => 'required|image|max:5120',
            'tipo'   => 'required|in:LOGO,LANCAMENTO',
        ]);

        $imagem = $this->service->store(
            $request->file('imagem'),
            $request->tipo,
            $request->user()->id
        );

        return response()->json(['data' => $imagem, 'message' => 'Imagem enviada.'], 201);
    }
}
