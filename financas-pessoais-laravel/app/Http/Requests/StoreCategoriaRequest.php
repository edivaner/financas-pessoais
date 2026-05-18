<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoriaRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nome'      => 'required|string|max:100',
            'cor'       => 'required|string|max:20',
            'icone'     => 'required|string|max:100',
            'tipo'      => 'required|in:DESPESAS,CREDITO,INVESTIMENTOS',
            'ativo'     => 'sometimes|boolean',
            'essencial' => 'sometimes|boolean',
        ];
    }
}
