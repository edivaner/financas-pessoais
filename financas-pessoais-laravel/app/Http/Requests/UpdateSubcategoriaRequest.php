<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSubcategoriaRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nome'  => 'sometimes|string|max:100',
            'cor'   => 'sometimes|string|max:20',
            'icone' => 'sometimes|string|max:100',
            'ativo' => 'sometimes|boolean',
        ];
    }
}
