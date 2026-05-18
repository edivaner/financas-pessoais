<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Categoria;
use App\Domain\Common\TipoCategoria;

class CategoriasSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = [
            // DESPESAS
            ['nome' => 'Academia', 'tipo' => TipoCategoria::DESPESAS, 'icone' => 'bi bi-activity', 'cor' => '#e74c3c', 'essencial' => false],
            ['nome' => 'Alimentação', 'tipo' => TipoCategoria::DESPESAS, 'icone' => 'bi-egg-fried', 'cor' => '#f39c12', 'essencial' => true],
            ['nome' => 'Assinaturas', 'tipo' => TipoCategoria::DESPESAS, 'icone' => 'bi-calendar-check', 'cor' => '#9b59b6', 'essencial' => false],
            ['nome' => 'Bebidas', 'tipo' => TipoCategoria::DESPESAS, 'icone' => 'bi-cup-hot', 'cor' => '#e67e22', 'essencial' => false],
            ['nome' => 'Bem-estar', 'tipo' => TipoCategoria::DESPESAS, 'icone' => 'bi-heart', 'cor' => '#e91e63', 'essencial' => false],
            ['nome' => 'Compras e Lazer', 'tipo' => TipoCategoria::DESPESAS, 'icone' => 'bi-bag', 'cor' => '#f1c40f', 'essencial' => false],
            ['nome' => 'Educação', 'tipo' => TipoCategoria::DESPESAS, 'icone' => 'bi-book', 'cor' => '#3498db', 'essencial' => true],
            ['nome' => 'Emergências', 'tipo' => TipoCategoria::DESPESAS, 'icone' => 'bi-exclamation-triangle', 'cor' => '#e74c3c', 'essencial' => true],
            ['nome' => 'Empréstimos', 'tipo' => TipoCategoria::DESPESAS, 'icone' => 'bi-credit-card', 'cor' => '#95a5a6', 'essencial' => false],
            ['nome' => 'Entretenimento Digital', 'tipo' => TipoCategoria::DESPESAS, 'icone' => 'bi-controller', 'cor' => '#8e44ad', 'essencial' => false],
            ['nome' => 'Higiene', 'tipo' => TipoCategoria::DESPESAS, 'icone' => 'bi-droplet', 'cor' => '#16a085', 'essencial' => true],
            ['nome' => 'Hobbies', 'tipo' => TipoCategoria::DESPESAS, 'icone' => 'bi-palette', 'cor' => '#f39c12', 'essencial' => false],
            ['nome' => 'Impostos e Taxas', 'tipo' => TipoCategoria::DESPESAS, 'icone' => 'bi-receipt', 'cor' => '#34495e', 'essencial' => true],
            ['nome' => 'Manutenção e Reparos', 'tipo' => TipoCategoria::DESPESAS, 'icone' => 'bi-tools', 'cor' => '#7f8c8d', 'essencial' => false],
            ['nome' => 'Mercado', 'tipo' => TipoCategoria::DESPESAS, 'icone' => 'bi-cart', 'cor' => '#27ae60', 'essencial' => true],
            ['nome' => 'Moradia', 'tipo' => TipoCategoria::DESPESAS, 'icone' => 'bi-house', 'cor' => '#2c3e50', 'essencial' => true],
            ['nome' => 'Outros', 'tipo' => TipoCategoria::DESPESAS, 'icone' => 'bi-three-dots', 'cor' => '#95a5a6', 'essencial' => false],
            ['nome' => 'Pagamentos', 'tipo' => TipoCategoria::DESPESAS, 'icone' => 'bi-credit-card-2-front', 'cor' => '#2980b9', 'essencial' => false],
            ['nome' => 'Poupança', 'tipo' => TipoCategoria::DESPESAS, 'icone' => 'bi-piggy-bank', 'cor' => '#f1c40f', 'essencial' => false],
            ['nome' => 'Saúde', 'tipo' => TipoCategoria::DESPESAS, 'icone' => 'bi-hospital', 'cor' => '#e74c3c', 'essencial' => true],
            ['nome' => 'Seguros', 'tipo' => TipoCategoria::DESPESAS, 'icone' => 'bi-shield-check', 'cor' => '#27ae60', 'essencial' => false],
            ['nome' => 'Serviços Bancários', 'tipo' => TipoCategoria::DESPESAS, 'icone' => 'bi-bank', 'cor' => '#34495e', 'essencial' => false],
            ['nome' => 'Streaming', 'tipo' => TipoCategoria::DESPESAS, 'icone' => 'bi-play-circle', 'cor' => '#e91e63', 'essencial' => false],
            ['nome' => 'Transferências', 'tipo' => TipoCategoria::DESPESAS, 'icone' => 'bi-arrow-left-right', 'cor' => '#9b59b6', 'essencial' => false],
            ['nome' => 'Transporte', 'tipo' => TipoCategoria::DESPESAS, 'icone' => 'bi-car-front', 'cor' => '#3498db', 'essencial' => true],
            ['nome' => 'Viagem', 'tipo' => TipoCategoria::DESPESAS, 'icone' => 'bi-airplane', 'cor' => '#1abc9c', 'essencial' => false],

            // CRÉDITO
            ['nome' => 'Renda Extra', 'tipo' => TipoCategoria::CREDITO, 'icone' => 'bi-plus-circle', 'cor' => '#27ae60', 'essencial' => false],
            ['nome' => 'Salário', 'tipo' => TipoCategoria::CREDITO, 'icone' => 'bi-cash-coin', 'cor' => '#2ecc71', 'essencial' => true],

            // INVESTIMENTOS
            ['nome' => 'Investimentos', 'tipo' => TipoCategoria::INVESTIMENTOS, 'icone' => 'bi-graph-up', 'cor' => '#f39c12', 'essencial' => false],
        ];

        foreach ($categorias as $categoria) {
            Categoria::firstOrCreate(
                ['user_id' => null, 'nome' => $categoria['nome'], 'tipo' => $categoria['tipo']],
                ['ativo' => true, ...$categoria]
            );
        }
    }
}