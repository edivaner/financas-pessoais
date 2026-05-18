<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Domain\Common\Cargo;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasUuids, HasFactory, Notifiable;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'email',
        'password',
        'nome',
        'sobrenome',
        'telefone',
        'profissao',
        'imagem_id',
        'dark_mode',
        'currency',
        'staff',
        'cargo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'staff'     => 'boolean',
            'dark_mode' => 'boolean',
            'cargo' => Cargo::class,
        ];
    }

    // Relacionamentos
    public function contas(): HasMany
    {
        return $this->hasMany(Conta::class);
    }

    public function cartoes(): HasMany
    {
        return $this->hasMany(Cartao::class);
    }

    public function lancamentos(): HasMany
    {
        return $this->hasMany(Lancamento::class);
    }

    public function categorias(): HasMany
    {
        return $this->hasMany(Categoria::class);
    }

    public function subcategorias(): HasMany
    {
        return $this->hasMany(Subcategoria::class);
    }

    public function limites(): HasMany
    {
        return $this->hasMany(Limite::class);
    }

    public function imagens(): HasMany
    {
        return $this->hasMany(Imagem::class);
    }

    public function imagem(): BelongsTo
    {
        return $this->belongsTo(Imagem::class);
    }

    public function otpCodes(): HasMany
    {
        return $this->hasMany(OtpCode::class);
    }
}
