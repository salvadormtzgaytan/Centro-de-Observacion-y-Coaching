<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Builder;
use App\Models\GuideTemplate;
use App\Models\GuideResponse;
use App\Models\GuideItemResponse;

/**
 * @property int $id
 * @property string $key
 * @property string $name
 * @property int $order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, GuideTemplate> $guideTemplates
 * @property-read int|null $guide_templates_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, GuideResponse> $responses
 * @property-read int|null $responses_count
 * @method static Builder<static>|Division newModelQuery()
 * @method static Builder<static>|Division newQuery()
 * @method static Builder<static>|Division query()
 * @method static Builder<static>|Division whereCreatedAt($value)
 * @method static Builder<static>|Division whereId($value)
 * @method static Builder<static>|Division whereKey($value)
 * @method static Builder<static>|Division whereName($value)
 * @method static Builder<static>|Division whereOrder($value)
 * @method static Builder<static>|Division whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Division extends Model
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

    /**
     * Always order divisions by `order` ascending.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('ordered', function (Builder $builder) {
            $builder->orderBy('order', 'asc');
        });
    }

    /**
     * Plantillas asociadas a esta división.
     */
    public function guideTemplates()
    {
        return $this->hasMany(GuideTemplate::class);
    }

    /**
     * Todas las evaluaciones hechas con plantillas de esta división.
     */
    public function responses()
    {
        return $this->hasManyThrough(
            GuideResponse::class,
            GuideTemplate::class,
            'division_id',       // FK on guide_templates
            'guide_template_id', // FK on guide_responses
            'id',                // Local PK on divisions
            'id'                 // Local PK on guide_templates
        );
    }

    /**
     * Todas las respuestas de ítem para esta división.
     *
     * Dado que Eloquent no soporta 3 niveles de hasManyThrough, usamos un query personalizado.
     */
    public function itemResponses()
    {
        return GuideItemResponse::whereIn('guide_response_id', function ($query) {
            $query->select('id')
                ->from('guide_responses')
                ->whereIn('guide_template_id', function ($q2) {
                    $q2->select('id')
                        ->from('guide_templates')
                        ->where('division_id', $this->id);
                });
        });
    }
}
