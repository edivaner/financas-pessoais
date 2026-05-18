<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Domain\Common\PeriodicidadeLimite;

class Limite extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->id)) {
                $model->id = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

    protected $fillable = [
        'user_id',
        'categoria_id',
        'titulo',
        'descricao',
        'valor_limite',
        'valor_gasto_atual',
        'periodicidade',
        'ultimo_reset',
    ];

    protected function casts(): array
    {
        return [
            'valor_limite' => 'decimal:4',
            'valor_gasto_atual' => 'decimal:4',
            'periodicidade' => PeriodicidadeLimite::class,
            'ultimo_reset' => 'date',
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
}
