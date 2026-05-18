<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Domain\Common\TipoLancamento;

class Lancamento extends Model
{
    use HasFactory, SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;

    protected static function booted(): void
    {
        static::creating(function (self $lancamento) {
            if (empty($lancamento->id)) {
                $lancamento->id = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

    protected $fillable = [
        'user_id',
        'titulo',
        'descricao',
        'valor',
        'tipo_lancamento',
        'categoria_id',
        'subcategoria_id',
        'conta_origem_id',
        'conta_destino_id',
        'cartao_id',
        'tipo_cartao',
        'data',
        'esta_pago',
        'simulado',
        'parcela_total',
        'parcela_atual',
        'imagem_id',
        'para_saldo_investido',
    ];

    protected function casts(): array
    {
        return [
            'valor' => 'decimal:4',
            'tipo_lancamento' => TipoLancamento::class,
            'esta_pago' => 'boolean',
            'simulado' => 'boolean',
            'data' => 'date',
            'para_saldo_investido' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    public function subcategoria(): BelongsTo
    {
        return $this->belongsTo(Subcategoria::class);
    }

    public function contaOrigem(): BelongsTo
    {
        return $this->belongsTo(Conta::class, 'conta_origem_id');
    }

    public function contaDestino(): BelongsTo
    {
        return $this->belongsTo(Conta::class, 'conta_destino_id');
    }

    public function cartao(): BelongsTo
    {
        return $this->belongsTo(Cartao::class);
    }

    public function imagem(): BelongsTo
    {
        return $this->belongsTo(Imagem::class);
    }
}
