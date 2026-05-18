<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateContaRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nome'               => 'sometimes|string|max:100',
            'carteira'           => 'sometimes|boolean',
            'somar_tela_inicial' => 'sometimes|boolean',
            'saldo_investido'    => 'sometimes|numeric|min:0',
            'imagem_id'          => 'nullable|exists:imagens,id',
            'logo_path'          => 'nullable|string|max:255',
        ];
    }
}
