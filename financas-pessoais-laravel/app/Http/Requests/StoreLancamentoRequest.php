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
