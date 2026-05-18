<?php
// app/Http/Requests/UpdateLancamentoRequest.php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLancamentoRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'titulo'           => 'sometimes|string|max:150',
            'conta_origem_id'  => 'sometimes|exists:contas,id',
            'conta_destino_id' => 'nullable|exists:contas,id',
            'valor'            => 'sometimes|numeric|min:0.01',
            'data'             => 'sometimes|date',
            'descricao'        => 'nullable|string|max:500',
            'categoria_id'     => 'nullable|exists:categorias,id',
            'subcategoria_id'  => 'nullable|exists:subcategorias,id',
        ];
    }
}
