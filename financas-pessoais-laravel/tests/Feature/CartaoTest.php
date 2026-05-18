<?php
use App\Models\User;
use App\Models\Conta;
use App\Models\Cartao;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can list own cartoes', function () {
    $user  = User::factory()->create();
    $conta = Conta::factory()->create(['user_id' => $user->id]);
    Cartao::factory()->count(2)->create(['user_id' => $user->id, 'conta_id' => $conta->id]);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/cartoes')
        ->assertOk()
        ->assertJsonCount(2, 'data');
});

test('user can create cartao', function () {
    $user  = User::factory()->create();
    $conta = Conta::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/cartoes', [
            'conta_id'        => $conta->id,
            'nome'            => 'Nubank Black',
            'tipo'            => 'CREDITO',
            'limite_total'    => 8000,
            'data_fechamento' => now()->addDays(15)->toDateString(),
        ])
        ->assertCreated()
        ->assertJsonPath('data.nome', 'Nubank Black');
});

test('user cannot update other user cartao', function () {
    $user   = User::factory()->create();
    $other  = User::factory()->create();
    $conta  = Conta::factory()->create(['user_id' => $other->id]);
    $cartao = Cartao::factory()->create(['user_id' => $other->id, 'conta_id' => $conta->id]);

    $this->actingAs($user, 'sanctum')
        ->putJson("/api/cartoes/{$cartao->id}", ['nome' => 'Hack'])
        ->assertNotFound();
});

test('user can delete own cartao', function () {
    $user   = User::factory()->create();
    $conta  = Conta::factory()->create(['user_id' => $user->id]);
    $cartao = Cartao::factory()->create(['user_id' => $user->id, 'conta_id' => $conta->id]);

    $this->actingAs($user, 'sanctum')
        ->deleteJson("/api/cartoes/{$cartao->id}")
        ->assertOk();

    $this->assertSoftDeleted('cartoes', ['id' => $cartao->id]);
});
