<?php
// tests/Feature/LancamentoTest.php
use App\Models\User;
use App\Models\Conta;
use App\Models\Lancamento;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can create receita and saldo increases', function () {
    $user  = User::factory()->create();
    $conta = Conta::factory()->create(['user_id' => $user->id, 'saldo' => 0]);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/lancamentos', [
            'titulo'          => 'Salário',
            'valor'           => 3000.00,
            'tipo_lancamento' => 'RECEITAS',
            'conta_origem_id' => $conta->id,
            'data'            => now()->toDateString(),
        ])
        ->assertCreated();

    $this->assertDatabaseHas('contas', ['id' => $conta->id, 'saldo' => '3000.0000']);
});

test('user can create despesa and saldo decreases', function () {
    $user  = User::factory()->create();
    $conta = Conta::factory()->create(['user_id' => $user->id, 'saldo' => 1000]);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/lancamentos', [
            'titulo'          => 'Aluguel',
            'valor'           => 500.00,
            'tipo_lancamento' => 'DESPESAS',
            'conta_origem_id' => $conta->id,
            'data'            => now()->toDateString(),
        ])
        ->assertCreated();

    $this->assertDatabaseHas('contas', ['id' => $conta->id, 'saldo' => '500.0000']);
});

test('deleting lancamento reverses saldo', function () {
    $user  = User::factory()->create();
    $conta = Conta::factory()->create(['user_id' => $user->id, 'saldo' => 0]);

    $res = $this->actingAs($user, 'sanctum')
        ->postJson('/api/lancamentos', [
            'titulo'          => 'Salário',
            'valor'           => 1000,
            'tipo_lancamento' => 'RECEITAS',
            'conta_origem_id' => $conta->id,
            'data'            => now()->toDateString(),
        ]);

    $id = $res->json('data.id');

    $this->actingAs($user, 'sanctum')
        ->deleteJson("/api/lancamentos/{$id}")
        ->assertOk();

    $this->assertDatabaseHas('contas', ['id' => $conta->id, 'saldo' => '0.0000']);
});

test('parcelamento creates N lancamentos', function () {
    $user  = User::factory()->create();
    $conta = Conta::factory()->create(['user_id' => $user->id, 'saldo' => 0]);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/lancamentos', [
            'titulo'          => 'Compra parcelada',
            'valor'           => 300,
            'tipo_lancamento' => 'DESPESAS',
            'conta_origem_id' => $conta->id,
            'parcela_total'   => 3,
            'data'            => now()->toDateString(),
        ])
        ->assertCreated();

    $this->assertDatabaseCount('lancamentos', 3);
});

test('user can list lancamentos filtered by mes/ano', function () {
    $user  = User::factory()->create();
    $conta = Conta::factory()->create(['user_id' => $user->id]);

    Lancamento::factory()->create(['user_id' => $user->id, 'conta_origem_id' => $conta->id, 'data' => now()]);
    Lancamento::factory()->create(['user_id' => $user->id, 'conta_origem_id' => $conta->id, 'data' => now()->subYear()]);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/lancamentos?mes=' . now()->month . '&ano=' . now()->year)
        ->assertOk()
        ->assertJsonCount(1, 'data');
});
