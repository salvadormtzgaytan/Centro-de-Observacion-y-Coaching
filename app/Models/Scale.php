<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Scale extends Model
{
    use HasFactory;

    protected $fillable = [
        'label',
        'value',
        'order',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'order' => 'integer',
    ];

    /**
     * Ordenar siempre por el campo `order` de forma ascendente.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('ordered', function (Builder $builder) {
            $builder->orderBy('order');
        });
    }
}
