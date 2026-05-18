<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Domain\Common\TipoCartao;

class Cartao extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'cartoes';
    protected $keyType = 'string';
    public $incrementing = false;

    protected static function booted(): void
    {
        static::creating(function (self $cartao) {
            if (empty($cartao->id)) {
                $cartao->id = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

    protected $fillable = [
        'id',
        'user_id',
        'conta_id',
        'nome',
        'tipo',
        'fatura_total',
        'limite_total',
        'dia_vencimento',
        'dias_antes_fechamento',
        'imagem_id',
        'logo_path',
    ];

    protected function casts(): array
    {
        return [
            'tipo' => TipoCartao::class,
            'fatura_total' => 'decimal:4',
            'limite_total' => 'decimal:4',
            'dia_vencimento' => 'integer',
            'dias_antes_fechamento' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function conta(): BelongsTo
    {
        return $this->belongsTo(Conta::class);
    }

    public function imagem(): BelongsTo
    {
        return $this->belongsTo(Imagem::class);
    }

    public function lancamentos(): HasMany
    {
        return $this->hasMany(Lancamento::class);
    }
}
