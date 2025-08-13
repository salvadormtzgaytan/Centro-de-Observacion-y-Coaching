<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
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
 * @method static Builder<static>|Channel newModelQuery()
 * @method static Builder<static>|Channel newQuery()
 * @method static Builder<static>|Channel query()
 * @method static Builder<static>|Channel whereCreatedAt($value)
 * @method static Builder<static>|Channel whereId($value)
 * @method static Builder<static>|Channel whereKey($value)
 * @method static Builder<static>|Channel whereName($value)
 * @method static Builder<static>|Channel whereOrder($value)
 * @method static Builder<static>|Channel whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Channel extends Model
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
     * Always order channels by `order` ascending.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('ordered', function (Builder $builder) {
            $builder->orderBy('order', 'asc');
        });
    }

    /**
     * Plantillas asociadas a este canal.
     */
    public function guideTemplates(): HasMany
    {
        return $this->hasMany(GuideTemplate::class);
    }

    /**
     * Todas las evaluaciones hechas con plantillas de este canal.
     */
    public function responses(): HasManyThrough
    {
        return $this->hasManyThrough(
            GuideResponse::class,
            GuideTemplate::class,
            'channel_id',        // FK on guide_templates
            'guide_template_id', // FK on guide_responses
            'id',                // Local PK on channels
            'id'                 // Local PK on guide_templates
        );
    }

    /**
     * Todas las respuestas de ítem para este canal.
     *
     * NOTE: This goes channel → template → response → itemResponse.
     * Laravel's hasManyThrough only supports two levels, so we aggregate via guide_responses.
     */
    public function itemResponses()
    {
        // First get all guide_response IDs for this channel
        return GuideItemResponse::whereIn('guide_response_id', function ($query) {
            $query->select('id')
                ->from('guide_responses')
                ->whereIn('guide_template_id', function ($q2) {
                    $q2->select('id')
                        ->from('guide_templates')
                        ->where('channel_id', $this->id);
                });
        });
    }
}
