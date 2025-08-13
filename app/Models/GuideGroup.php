<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property bool $active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GuideTemplate> $templates
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GuideTemplate> $publishedTemplates
 * @property-read int|null $templates_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuideGroup active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuideGroup withPublishedTemplates()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuideGroup newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuideGroup newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuideGroup onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuideGroup query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuideGroup whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuideGroup whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuideGroup whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuideGroup whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuideGroup whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuideGroup whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuideGroup whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuideGroup withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuideGroup withoutTrashed()
 *
 * @mixin \Eloquent
 */
class GuideGroup extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'description', 'active'];

    protected $casts = [
        'active' => 'boolean',
    ];

    /**
     * Plantillas de guía asociadas a este grupo (todas).
     */
    public function templates(): BelongsToMany
    {
        return $this->belongsToMany(
            GuideTemplate::class,
            'guide_group_template',   // pivote real en tu BD
            'guide_group_id',
            'guide_template_id'
        )->withTimestamps();
    }

    /**
     * Backward-compat alias to avoid "undefined method guideTemplates()".
     * Delegates to templates().
     */
    public function guideTemplates(): BelongsToMany
    {
        return $this->templates();
    }

    /**
     * Plantillas PUBLICADAS asociadas a este grupo.
     * Cambia 'published' por GuideTemplate::STATUS_PUBLISHED si tienes la constante.
     */
    public function publishedTemplates(): BelongsToMany
    {
        return $this->templates()
            ->where('guide_templates.status', GuideTemplate::STATUS_PUBLISHED);
    }

    /**
     * Scope: solo grupos activos.
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope: grupos que tienen al menos una plantilla publicada.
     */
    public function scopeWithPublishedTemplates($query)
    {
        return $query->whereHas('publishedTemplates');
    }
}
