<?php
namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ContaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id'                 => (string) Str::uuid(),
            'user_id'            => User::factory(),
            'nome'               => $this->faker->word() . ' Bank',
            'saldo'              => $this->faker->randomFloat(2, 0, 5000),
            'saldo_investido'    => 0,
            'carteira'           => false,
            'somar_tela_inicial' => true,
            'imagem_id'          => null,
        ];
    }
}
