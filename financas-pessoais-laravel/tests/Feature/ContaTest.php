<?php
use App\Models\User;
use App\Models\Conta;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('unauthenticated cannot list contas', function () {
    $this->getJson('/api/contas')->assertUnauthorized();
});

test('user can list own contas', function () {
    $user = User::factory()->create();
    Conta::factory()->count(2)->create(['user_id' => $user->id]);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/contas')
        ->assertOk()
        ->assertJsonCount(2, 'data');
});

test('user cannot see other user contas', function () {
    $user  = User::factory()->create();
    $other = User::factory()->create();
    Conta::factory()->create(['user_id' => $other->id]);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/contas')
        ->assertOk()
        ->assertJsonCount(0, 'data');
});

test('user can create conta', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/contas', [
            'nome'               => 'Nubank',
            'saldo'              => 1500.50,
            'saldo_investido'    => 0,
            'carteira'           => false,
            'somar_tela_inicial' => true,
        ])
        ->assertCreated()
        ->assertJsonPath('data.nome', 'Nubank');
});

test('create conta requires nome', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/contas', ['saldo' => 100])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['nome']);
});

test('user can update own conta', function () {
    $user  = User::factory()->create();
    $conta = Conta::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user, 'sanctum')
        ->putJson("/api/contas/{$conta->id}", ['nome' => 'Updated'])
        ->assertOk()
        ->assertJsonPath('data.nome', 'Updated');
});

test('user cannot update other user conta', function () {
    $user  = User::factory()->create();
    $other = User::factory()->create();
    $conta = Conta::factory()->create(['user_id' => $other->id]);

    $this->actingAs($user, 'sanctum')
        ->putJson("/api/contas/{$conta->id}", ['nome' => 'Hack'])
        ->assertNotFound();
});

test('user can delete own conta', function () {
    $user  = User::factory()->create();
    $conta = Conta::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user, 'sanctum')
        ->deleteJson("/api/contas/{$conta->id}")
        ->assertOk();

    $this->assertSoftDeleted('contas', ['id' => $conta->id]);
});
