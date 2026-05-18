<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCartaoRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'conta_id'        => 'required|exists:contas,id',
            'nome'            => 'required|string|max:100',
            'tipo'            => 'required|in:CREDITO,DEBITO,MULTIPLO',
            'limite_total'          => 'required|numeric|min:0',
            'dia_vencimento'        => 'nullable|integer|min:1|max:31',
            'dias_antes_fechamento' => 'nullable|integer|min:1|max:28',
            'imagem_id'             => 'nullable|exists:imagens,id',
            'logo_path'             => 'nullable|string|max:255',
        ];
    }
}
