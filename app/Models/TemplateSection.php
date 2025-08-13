<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use App\Models\GuideTemplate;
use App\Models\TemplateItem;
use App\Models\GuideItemResponse;

/**
 * @property int $id
 * @property int $guide_template_id
 * @property string $title
 * @property int $order
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read GuideTemplate $guideTemplate
 * @property-read \Illuminate\Database\Eloquent\Collection<int, GuideItemResponse> $itemResponses
 * @property-read int|null $item_responses_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, TemplateItem> $items
 * @property-read int|null $items_count
 * @method static Builder<static>|TemplateSection newModelQuery()
 * @method static Builder<static>|TemplateSection newQuery()
 * @method static Builder<static>|TemplateSection onlyTrashed()
 * @method static Builder<static>|TemplateSection query()
 * @method static Builder<static>|TemplateSection whereCreatedAt($value)
 * @method static Builder<static>|TemplateSection whereDeletedAt($value)
 * @method static Builder<static>|TemplateSection whereGuideTemplateId($value)
 * @method static Builder<static>|TemplateSection whereId($value)
 * @method static Builder<static>|TemplateSection whereOrder($value)
 * @method static Builder<static>|TemplateSection whereTitle($value)
 * @method static Builder<static>|TemplateSection whereUpdatedAt($value)
 * @method static Builder<static>|TemplateSection withTrashed(bool $withTrashed = true)
 * @method static Builder<static>|TemplateSection withoutTrashed()
 * @mixin \Eloquent
 */
class TemplateSection extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'guide_template_id',
        'title',
        'order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'order' => 'integer',
    ];

    /**
     * Always order sections by `order` ascending.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('ordered', function (Builder $builder) {
            $builder->orderBy('order', 'asc');
        });
    }

    /**
     * Guide template to which this section belongs.
     */
    public function guideTemplate()
    {
        return $this->belongsTo(GuideTemplate::class, 'guide_template_id');
    }

    /**
     * Items in this section, ordered by `order`.
     */
    public function items()
    {
        return $this->hasMany(TemplateItem::class, 'template_section_id')
            ->orderBy('order', 'asc');
    }

    /**
     * All item responses associated with this section, via its items.
     */
    public function itemResponses()
    {
        return $this->hasManyThrough(
            GuideItemResponse::class,
            TemplateItem::class,
            'template_section_id', // FK on template_items
            'template_item_id',    // FK on guide_item_responses
            'id',                  // Local PK on template_sections
            'id'                   // Local PK on template_items
        );
    }

    /**
     * Get responses that have or can have a score.
     * These are responses for items of types that are scorable (select, radio, scale).
     */
    public function scoredResponses()
    {
        return $this->itemResponses()
            ->whereHas('templateItem', function ($query) {
                $query->whereIn('type', TemplateItem::allowedScorable());
            });
    }
}
