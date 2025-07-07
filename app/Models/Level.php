<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Level extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'name',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    public function guideTemplates()
    {
        return $this->hasMany(GuideTemplate::class);
    }

    public function responses()
    {
        return $this->hasManyThrough(
            \App\Models\GuideResponse::class,
            \App\Models\GuideTemplate::class,
            'level_id',
            'guide_template_id'
        );
    }

    public function itemResponses()
    {
        return $this->hasManyThrough(
            \App\Models\GuideItemResponse::class,
            \App\Models\GuideResponse::class,
            'guide_template_id',
            'guide_response_id'
        );
    }

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
