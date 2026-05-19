<?php
// app/Http/Requests/StoreLancamentoRequest.php
namespace App\Http\Requests;

use App\Models\Cartao;
use App\Models\Subcategoria;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLancamentoRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $tipo      = $this->input('tipo_lancamento');
            $userId    = $this->user()->id;
            $origemId  = $this->input('conta_origem_id');
            $destinoId = $this->input('conta_destino_id');
            $cartaoId  = $this->input('cartao_id');
            $tipoCartao = $this->input('tipo_cartao');
            $categoriaId = $this->input('categoria_id');
            $subcategoriaId = $this->input('subcategoria_id');

            // Transferência: destino obrigatório quando não vai para saldo investido
            if ($tipo === 'TRANSFERENCIA' && !$this->boolean('para_saldo_investido') && !$destinoId) {
                $v->errors()->add('conta_destino_id', 'Conta de destino é obrigatória na transferência.');
            }

            // Transferência: origem ≠ destino
            if ($tipo === 'TRANSFERENCIA' && $origemId && $destinoId && $origemId === $destinoId) {
                $v->errors()->add('conta_destino_id', 'Conta de destino deve ser diferente da conta de origem.');
            }

            // Cartão deve pertencer à conta de origem
            if ($cartaoId && $origemId) {
                $cartao = Cartao::where('id', $cartaoId)->where('conta_id', $origemId)->where('user_id', $userId)->first();
                if (!$cartao) {
                    $v->errors()->add('cartao_id', 'O cartão não pertence à conta selecionada.');
                } elseif ($tipoCartao && $cartao->tipo->value !== 'MULTIPLO' && $cartao->tipo->value !== $tipoCartao) {
                    // tipo_cartao deve ser compatível com o tipo do cartão
                    $v->errors()->add('tipo_cartao', 'Tipo de pagamento incompatível com o cartão selecionado.');
                }
            }

            // Subcategoria deve pertencer à categoria selecionada
            if ($subcategoriaId && $categoriaId) {
                $ok = Subcategoria::where('id', $subcategoriaId)->where('categoria_id', $categoriaId)->exists();
                if (!$ok) {
                    $v->errors()->add('subcategoria_id', 'Subcategoria não pertence à categoria selecionada.');
                }
            }
        });
    }

    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'titulo'               => 'required|string|max:150',
            'descricao'            => 'nullable|string|max:500',
            'valor'                => 'required|numeric|min:0.01',
            'tipo_lancamento'      => 'required|in:RECEITAS,DESPESAS,TRANSFERENCIA,INVESTIMENTOS',
            'conta_origem_id'      => ['required', Rule::exists('contas', 'id')->where('user_id', $userId)],
            'conta_destino_id'     => ['nullable', Rule::exists('contas', 'id')->where('user_id', $userId)],
            'cartao_id'            => ['nullable', Rule::exists('cartoes', 'id')->where('user_id', $userId)],
            'tipo_cartao'          => 'nullable|in:CREDITO,DEBITO,INVESTIR,RESGATAR',
            'categoria_id'         => ['nullable', Rule::exists('categorias', 'id')->where('user_id', $userId)],
            'subcategoria_id'      => 'nullable|exists:subcategorias,id',
            'data'                 => 'required|date',
            'parcela_total'        => 'nullable|integer|min:1|max:60',
            'para_saldo_investido' => 'sometimes|boolean',
            'imagem_id'            => 'nullable|exists:imagens,id',
        ];
    }
}
