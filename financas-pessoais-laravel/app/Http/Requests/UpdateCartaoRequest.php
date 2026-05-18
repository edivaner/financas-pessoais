<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCartaoRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nome'            => 'sometimes|string|max:100',
            'tipo'            => 'sometimes|in:CREDITO,DEBITO,MULTIPLO',
            'limite_total'          => 'sometimes|numeric|min:0',
            'dia_vencimento'        => 'sometimes|nullable|integer|min:1|max:31',
            'dias_antes_fechamento' => 'sometimes|nullable|integer|min:1|max:28',
            'imagem_id'             => 'nullable|exists:imagens,id',
            'logo_path'             => 'nullable|string|max:255',
        ];
    }
}
