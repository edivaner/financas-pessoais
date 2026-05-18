<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLimiteRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'categoria_id' => 'required|exists:categorias,id',
            'titulo'       => 'required|string|max:100',
            'descricao'    => 'nullable|string|max:255',
            'valor_limite' => 'required|numeric|min:0.01',
        ];
    }
}
