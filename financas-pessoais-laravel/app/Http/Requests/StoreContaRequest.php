<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContaRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nome'               => 'required|string|max:100',
            'saldo'              => 'required|numeric|min:0',
            'saldo_investido'    => 'sometimes|numeric|min:0',
            'carteira'           => 'sometimes|boolean',
            'somar_tela_inicial' => 'sometimes|boolean',
            'imagem_id'          => 'nullable|exists:imagens,id',
            'logo_path'          => 'nullable|string|max:255',
        ];
    }
}
