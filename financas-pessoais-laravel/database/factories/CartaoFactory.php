<?php
namespace Database\Factories;

use App\Models\Conta;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CartaoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id'              => (string) Str::uuid(),
            'user_id'         => User::factory(),
            'conta_id'        => Conta::factory(),
            'nome'            => $this->faker->word() . ' Card',
            'tipo'            => 'CREDITO',
            'fatura_total'    => 0,
            'limite_total'    => 5000,
            'data_fechamento' => now()->addDays(10),
            'imagem_id'       => null,
        ];
    }
}
