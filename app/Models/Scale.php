<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $label
 * @property numeric $value
 * @property int $order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, TemplateItem> $templateItems
 * @property-read int|null $template_items_count
 *
 * @method static Builder<static>|Scale newModelQuery()
 * @method static Builder<static>|Scale newQuery()
 * @method static Builder<static>|Scale query()
 * @method static Builder<static>|Scale whereCreatedAt($value)
 * @method static Builder<static>|Scale whereId($value)
 * @method static Builder<static>|Scale whereLabel($value)
 * @method static Builder<static>|Scale whereOrder($value)
 * @method static Builder<static>|Scale whereUpdatedAt($value)
 * @method static Builder<static>|Scale whereValue($value)
 *
 * @mixin \Eloquent
 */
class Scale extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'label',
        'value',
        'order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'value' => 'decimal:2',
        'order' => 'integer',
    ];

    /**
     * Always order scales by `order` ascending.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('ordered', function (Builder $builder) {
            $builder->orderBy('order', 'asc');
        });
    }

    /**
     * Template items that use this scale.
     */
    public function templateItems()
    {
        return $this->hasMany(TemplateItem::class, 'options', 'value');
    }

    /**
     * Obtiene el valor máximo de todas las escalas disponibles
     */
    public static function getMaxScaleValue(): float
    {
        return (float) self::max('value');
    }

    /**
     * Obtiene la etiqueta (label) correspondiente a un valor dado.
     */
    public static function getLabelForValue(float|int|string $value): ?string
    {
        return self::query()
            ->where('value', $value)
            ->value('label');
    }
}
