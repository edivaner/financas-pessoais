<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLimiteRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'categoria_id' => ['required', \Illuminate\Validation\Rule::exists('categorias', 'id')->where('user_id', $userId)],
            'titulo'       => 'required|string|max:25',
            'descricao'    => 'nullable|string|max:255',
            'valor_limite' => 'required|numeric|min:0.01',
        ];
    }
}
