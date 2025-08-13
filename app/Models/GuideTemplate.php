<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property int $id
 * @property string $name
 * @property int $level_id
 * @property int $channel_id
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Channel $channel
 * @property-read Level $level
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TemplateSection> $sections
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GuideResponse> $responses
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GuideItemResponse> $itemResponses
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GuideGroup> $groups
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read string $status_label
 *
 * @method static Builder<static>|GuideTemplate published()
 * @method static Builder<static>|GuideTemplate draft()
 * @method static Builder<static>|GuideTemplate selectableForCoach()
 * @method static Builder<static>|GuideTemplate newModelQuery()
 * @method static Builder<static>|GuideTemplate newQuery()
 * @method static Builder<static>|GuideTemplate onlyTrashed()
 * @method static Builder<static>|GuideTemplate query()
 * @method static Builder<static>|GuideTemplate whereChannelId($value)
 * @method static Builder<static>|GuideTemplate whereCreatedAt($value)
 * @method static Builder<static>|GuideTemplate whereDeletedAt($value)
 * @method static Builder<static>|GuideTemplate whereId($value)
 * @method static Builder<static>|GuideTemplate whereLevelId($value)
 * @method static Builder<static>|GuideTemplate whereName($value)
 * @method static Builder<static>|GuideTemplate whereStatus($value)
 * @method static Builder<static>|GuideTemplate whereUpdatedAt($value)
 * @method static Builder<static>|GuideTemplate withTrashed(bool $withTrashed = true)
 * @method static Builder<static>|GuideTemplate withoutTrashed()
 *
 * @mixin \Eloquent
 */
class GuideTemplate extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    /** Estados permitidos en BD (enum: 'draft','published') */
    public const STATUS_DRAFT = 'draft';

    public const STATUS_PUBLISHED = 'published';

    /**
     * Atributos asignables en masa.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'name',
        'level_id',
        'channel_id',
        'status',
    ];

    /**
     * Casts nativos.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'status' => 'string',
    ];

    /**
     * Scope global: ordenar por nombre ASC.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('name_sorted', function (Builder $builder) {
            $builder->orderBy('name', 'asc');
        });
    }

    /**
     * Spatie activity log options.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'name',
                'level_id',
                'channel_id',
                'status',
            ])
            ->useLogName('Plantilla de Guía')
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn (string $eventName) => "Plantilla de Guía fue {$eventName}");
    }

    /**
     * Personaliza la descripción del activity log con campos modificados.
     */
    public function getDescriptionForEvent(string $eventName): string
    {
        $changed = collect($this->getDirty())->keys()->implode(', ');
        $base = "Plantilla de Guía fue {$eventName}";

        return $changed ? "{$base}. Campos modificados: {$changed}" : $base;
    }

    /**
     * Agrega IP del request a las props del activity log.
     */
    public function tapActivity(Activity $activity, string $eventName): void
    {
        $activity->properties = $activity->properties->merge([
            'ip' => request()->ip(),
        ]);
    }

    /**
     * Nivel asociado a la plantilla.
     */
    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    /**
     * Canal asociado a la plantilla.
     */
    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }

    /**
     * Secciones hijas ordenadas por `order`.
     */
    public function sections(): HasMany
    {
        return $this->hasMany(TemplateSection::class)->orderBy('order', 'asc');
    }

    /**
     * Respuestas (GuideResponse) realizadas con esta plantilla.
     */
    public function responses(): HasMany
    {
        return $this->hasMany(GuideResponse::class, 'guide_template_id');
    }

    /**
     * Respuestas de ítem a través de las evaluaciones.
     */
    public function itemResponses(): HasManyThrough
    {
        return $this->hasManyThrough(
            GuideItemResponse::class,
            GuideResponse::class,
            'guide_template_id',   // FK en guide_responses
            'guide_response_id',   // FK en guide_item_responses
            'id',                  // PK en guide_templates
            'id'                   // PK en guide_responses
        );
    }

    /**
     * Grupos asociados (pivot con timestamps).
     */
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(
            GuideGroup::class,
            'guide_group_template',
            'guide_template_id',
            'guide_group_id'
        )->withTimestamps();
    }

    /**
     * Backward-compat alias to avoid "undefined method guideGroups()".
     * Delegates to groups().
     */
    public function guideGroups(): BelongsToMany
    {
        return $this->groups();
    }

    /* =========================
     * Scopes de estado
     * ========================= */

    /** Solo publicadas. */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    /** Solo borrador. */
    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    /**
     * Helper para UI de coach: solo plantillas elegibles para uso.
     * (Actualmente, equivalentes a publicadas).
     */
    public function scopeSelectableForCoach(Builder $query): Builder
    {
        return $query->published();
    }

    /* =========================
     * Helpers / Accessors
     * ========================= */

    /**
     * Mapa de etiquetas de estado en español.
     */
    public static function statusLabels(): array
    {
        return [
            self::STATUS_DRAFT => 'Borrador',
            self::STATUS_PUBLISHED => 'Publicada',
        ];
    }

    /**
     * Accessor: etiqueta legible del estado.
     */
    public function getStatusLabelAttribute(): string
    {
        return self::statusLabels()[$this->status] ?? ucfirst($this->status);
    }

    /**
     * ¿Está publicada?
     */
    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    /**
     * Normaliza el nombre:
     * - Trim a ambos lados
     * - Colapsa espacios múltiples en uno
     * Nota: NO cambia mayúsculas/minúsculas.
     */
    protected function name(): Attribute
    {
        return Attribute::make(
            set: fn (mixed $value) => is_string($value)
                ? trim(preg_replace('/\s+/', ' ', $value))
                : $value
        );
    }
}
