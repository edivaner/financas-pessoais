<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Conta extends Model
{
    use HasFactory, SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;

    protected static function booted(): void
    {
        static::creating(function (self $conta) {
            if (empty($conta->id)) {
                $conta->id = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

    protected $fillable = [
        'user_id',
        'carteira',
        'nome',
        'saldo',
        'saldo_investido',
        'somar_tela_inicial',
        'imagem_id',
        'logo_path',
    ];

    protected function casts(): array
    {
        return [
            'carteira' => 'boolean',
            'saldo' => 'decimal:4',
            'saldo_investido' => 'decimal:4',
            'somar_tela_inicial' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function imagem(): BelongsTo
    {
        return $this->belongsTo(Imagem::class);
    }

    public function cartoes(): HasMany
    {
        return $this->hasMany(Cartao::class);
    }

    public function lancamentosOrigem(): HasMany
    {
        return $this->hasMany(Lancamento::class, 'conta_origem_id');
    }

    public function lancamentosDestino(): HasMany
    {
        return $this->hasMany(Lancamento::class, 'conta_destino_id');
    }
}
