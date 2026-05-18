<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubcategoriaRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'categoria_id' => 'required|exists:categorias,id',
            'nome'         => 'required|string|max:100',
            'cor'          => 'required|string|max:20',
            'icone'        => 'required|string|max:100',
            'ativo'        => 'sometimes|boolean',
        ];
    }
}
