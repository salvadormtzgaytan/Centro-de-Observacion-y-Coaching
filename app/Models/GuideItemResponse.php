<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


/**
 * @property int $id
 * @property int $guide_response_id
 * @property int $template_item_id
 * @property array<array-key, mixed>|null $answer
 * @property numeric $score_obtained
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\TemplateItem $item
 * @property-read \App\Models\GuideResponse $response
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuideItemResponse newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuideItemResponse newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuideItemResponse query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuideItemResponse whereAnswer($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuideItemResponse whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuideItemResponse whereGuideResponseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuideItemResponse whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuideItemResponse whereScoreObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuideItemResponse whereTemplateItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuideItemResponse whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class GuideItemResponse extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'guide_response_id',
        'template_item_id',
        'answer',
        'score_obtained',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'answer'         => 'array',
        'score_obtained' => 'decimal:2',
    ];

    /**
     * Evaluate response this item belongs to.
     */
    public function response()
    {
        return $this->belongsTo(GuideResponse::class, 'guide_response_id');
    }

    /**
     * Template item this response refers to.
     */
    public function item()
    {
        return $this->belongsTo(TemplateItem::class, 'template_item_id');
    }
}
