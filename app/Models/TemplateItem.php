<?php

namespace App\Models;

use App\Utils\ActivityLogHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property int $id
 * @property int $template_section_id
 * @property string $question
 * @property string $type
 * @property string|null $help_text
 * @property array<array-key, mixed>|null $options
 * @property int $order
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, GuideItemResponse> $itemResponses
 * @property-read int|null $item_responses_count
 * @property-read TemplateSection $section
 *
 * @method static Builder<static>|TemplateItem newModelQuery()
 * @method static Builder<static>|TemplateItem newQuery()
 * @method static Builder<static>|TemplateItem onlyTrashed()
 * @method static Builder<static>|TemplateItem query()
 * @method static Builder<static>|TemplateItem whereCreatedAt($value)
 * @method static Builder<static>|TemplateItem whereDeletedAt($value)
 * @method static Builder<static>|TemplateItem whereHelpText($value)
 * @method static Builder<static>|TemplateItem whereId($value)
 * @method static Builder<static>|TemplateItem whereOptions($value)
 * @method static Builder<static>|TemplateItem whereOrder($value)
 * @method static Builder<static>|TemplateItem whereQuestion($value)
 * @method static Builder<static>|TemplateItem whereTemplateSectionId($value)
 * @method static Builder<static>|TemplateItem whereType($value)
 * @method static Builder<static>|TemplateItem whereUpdatedAt($value)
 * @method static Builder<static>|TemplateItem withTrashed(bool $withTrashed = true)
 * @method static Builder<static>|TemplateItem withoutTrashed()
 *
 * @mixin \Eloquent
 */
class TemplateItem extends Model
{
    use HasFactory;
    use LogsActivity;
    use SoftDeletes;

    // --- Tipos (constantes) ---
    public const TYPE_SELECT = 'select';

    public const TYPE_TEXT = 'text';

    public const TYPE_RADIO = 'radio';

    public const TYPE_SCALE = 'scale';

    private static ?array $cachedScales = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'template_section_id',
        'question',
        'type',
        'help_text',
        'options',
        'order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'options' => 'array',
        'order' => 'integer',
    ];

    /**
     * Always order items by `order` ascending.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('ordered', function (Builder $builder) {
            $builder->orderBy('order', 'asc');
        });
    }

    /**
     * Configure Spatie activity log options.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return ActivityLogHelper::getLogOptions(
            ['template_section_id', 'question', 'type', 'help_text', 'options', 'order'],
            'Ítem de Plantilla',
            'El ítem'
        );
    }

    /**
     * Customize the description for activity log events.
     */
    public function getDescriptionForEvent(string $eventName): string
    {
        return ActivityLogHelper::getDescriptionForEvent(
            $eventName,
            'El ítem',
            array_keys($this->getDirty())
        );
    }

    /**
     * Belongs to the parent section.
     */
    public function section()
    {
        return $this->belongsTo(TemplateSection::class, 'template_section_id');
    }

    /**
     * All item responses associated to this template item.
     */
    public function itemResponses()
    {
        return $this->hasMany(GuideItemResponse::class, 'template_item_id');
    }

    /**
     * Establece opciones de escala sin usar el mutator.
     */
    public function setRawOptionsFromScales(): void
    {
        $json = Scale::all(['label', 'value'])
            ->map(fn ($s) => ['label' => $s->label, 'value' => $s->value])
            ->toJson();

        $this->forceFill(['options' => $json]);
    }

    /**
     * Establece help_text basado en las escalas seleccionadas.
     *
     * @param  array<string>  $values
     */
    public function setHelpTextFromScaleValues(array $values): void
    {
        $text = Scale::whereIn('value', $values)
            ->get()
            ->map(fn ($s) => "{$s->label} = {$s->value}")
            ->implode(', ');

        $this->help_text = $text;
    }

    /**
     * Accessor to return only the stored values for form binding.
     */
    public function getOptionsAttribute($value)
    {
        $array = json_decode($value, true);
        if (is_array($array) && isset($array[0]['value'])) {
            return collect($array)
                ->pluck('value')
                ->map(fn ($v) => (string) $v)
                ->toArray();
        }

        return $array;
    }

    public static function allowedScorable(): array
    {
        return [
            self::TYPE_SELECT,
            self::TYPE_RADIO,
            self::TYPE_SCALE,
        ];
    }

    public function isScorable(): bool
    {
        return in_array($this->type, self::allowedScorable(), true);
    }

    /**
     * Obtiene escalas con cache en memoria
     */
    public function getScales(): array
    {
        if (self::$cachedScales !== null) {
            return self::$cachedScales;
        }

        $scales = Scale::orderBy('order')->get(['label', 'value']);

        return self::$cachedScales = [
            'options' => $scales->pluck('value')->map(fn ($v) => (string) $v)->values()->all(),
            'help' => $scales->map(fn ($s) => "{$s->label} = {$s->value}")->implode(', '),
        ];
    }
}
