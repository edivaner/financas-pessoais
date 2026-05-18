<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Categoria;
use App\Models\Subcategoria;

class SubcategoriasSeeder extends Seeder
{
    public function run(): void
    {
        // Buscar categorias padrão
        $mercado = Categoria::where('nome', 'Mercado')->whereNull('user_id')->first();
        $seguros = Categoria::where('nome', 'Seguros')->whereNull('user_id')->first();
        $manutencao = Categoria::where('nome', 'Manutenção e Reparos')->whereNull('user_id')->first();
        $impostos = Categoria::where('nome', 'Impostos e Taxas')->whereNull('user_id')->first();
        $emergencias = Categoria::where('nome', 'Emergências')->whereNull('user_id')->first();
        $educacao = Categoria::where('nome', 'Educação')->whereNull('user_id')->first();
        $emprestimos = Categoria::where('nome', 'Empréstimos')->whereNull('user_id')->first();
        $alimentacao = Categoria::where('nome', 'Alimentação')->whereNull('user_id')->first();

        $subcategorias = [
            // Mercado
            ['categoria_id' => $mercado->id, 'nome' => 'Padaria', 'icone' => 'bi-bread-slice', 'cor' => '#f39c12', 'essencial' => true],
            ['categoria_id' => $mercado->id, 'nome' => 'Mercearia', 'icone' => 'bi-shop', 'cor' => '#27ae60', 'essencial' => true],

            // Seguros
            ['categoria_id' => $seguros->id, 'nome' => 'Seguro de automóvel', 'icone' => 'bi-car-front', 'cor' => '#3498db', 'essencial' => false],
            ['categoria_id' => $seguros->id, 'nome' => 'Seguro de vida', 'icone' => 'bi-heart-pulse', 'cor' => '#e74c3c', 'essencial' => false],
            ['categoria_id' => $seguros->id, 'nome' => 'Seguro residencial', 'icone' => 'bi-house', 'cor' => '#2c3e50', 'essencial' => false],

            // Manutenção e Reparos
            ['categoria_id' => $manutencao->id, 'nome' => 'Reparos de eletrodomésticos', 'icone' => 'bi-tools', 'cor' => '#7f8c8d', 'essencial' => false],
            ['categoria_id' => $manutencao->id, 'nome' => 'Reparos domésticos', 'icone' => 'bi-hammer', 'cor' => '#95a5a6', 'essencial' => false],

            // Impostos e Taxas
            ['categoria_id' => $impostos->id, 'nome' => 'IPTU', 'icone' => 'bi-receipt', 'cor' => '#34495e', 'essencial' => true],
            ['categoria_id' => $impostos->id, 'nome' => 'IR', 'icone' => 'bi-file-text', 'cor' => '#2c3e50', 'essencial' => true],
            ['categoria_id' => $impostos->id, 'nome' => 'IPVA', 'icone' => 'bi-car-front', 'cor' => '#2980b9', 'essencial' => true],

            // Emergências
            ['categoria_id' => $emergencias->id, 'nome' => 'Despesas emergenciais', 'icone' => 'bi-exclamation-triangle', 'cor' => '#e74c3c', 'essencial' => true],

            // Educação
            ['categoria_id' => $educacao->id, 'nome' => 'Cursos', 'icone' => 'bi-book', 'cor' => '#3498db', 'essencial' => false],
            ['categoria_id' => $educacao->id, 'nome' => 'Livros', 'icone' => 'bi-journal-text', 'cor' => '#2980b9', 'essencial' => false],
            ['categoria_id' => $educacao->id, 'nome' => 'Mensalidades escolares', 'icone' => 'bi-mortarboard', 'cor' => '#2c3e50', 'essencial' => true],

            // Empréstimos
            ['categoria_id' => $emprestimos->id, 'nome' => 'Cartão de crédito', 'icone' => 'bi-credit-card', 'cor' => '#95a5a6', 'essencial' => false],
            ['categoria_id' => $emprestimos->id, 'nome' => 'Empréstimos pessoais', 'icone' => 'bi-bank', 'cor' => '#7f8c8d', 'essencial' => false],
            ['categoria_id' => $emprestimos->id, 'nome' => 'Financiamento', 'icone' => 'bi-house', 'cor' => '#34495e', 'essencial' => false],

            // Alimentação
            ['categoria_id' => $alimentacao->id, 'nome' => 'Cafeteria', 'icone' => 'bi-cup-hot', 'cor' => '#e67e22', 'essencial' => false],
            ['categoria_id' => $alimentacao->id, 'nome' => 'Delivery', 'icone' => 'bi-truck', 'cor' => '#f39c12', 'essencial' => false],
            ['categoria_id' => $alimentacao->id, 'nome' => 'Supermercado', 'icone' => 'bi-cart', 'cor' => '#27ae60', 'essencial' => true],
            ['categoria_id' => $alimentacao->id, 'nome' => 'Lanchonete', 'icone' => 'bi-egg-fried', 'cor' => '#f1c40f', 'essencial' => false],
        ];

        foreach ($subcategorias as $subcategoria) {
            Subcategoria::firstOrCreate(
                ['user_id' => null, 'nome' => $subcategoria['nome'], 'categoria_id' => $subcategoria['categoria_id']],
                ['ativo' => true, ...$subcategoria]
            );
        }
    }
}