<?php
// database/factories/LancamentoFactory.php
namespace Database\Factories;

use App\Models\User;
use App\Models\Conta;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class LancamentoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id'              => Str::uuid(),
            'user_id'         => User::factory(),
            'titulo'          => $this->faker->word(),
            'descricao'       => null,
            'valor'           => $this->faker->randomFloat(2, 10, 500),
            'tipo_lancamento' => 'RECEITAS',
            'conta_origem_id' => Conta::factory(),
            'data'            => now()->toDateString(),
            'esta_pago'       => true,
            'simulado'        => false,
            'parcela_total'   => null,
            'parcela_atual'   => 1,
            'para_saldo_investido' => false,
        ];
    }
}
