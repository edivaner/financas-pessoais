<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLimiteRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'titulo'       => 'sometimes|string|max:100',
            'descricao'    => 'nullable|string|max:255',
            'valor_limite' => 'sometimes|numeric|min:0.01',
        ];
    }
}
