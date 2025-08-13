<?php

namespace App\Models;

use App\Data\Guide\SectionAverageData;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property int $session_id
 * @property int $guide_template_id
 * @property numeric $total_score
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\GuideTemplate $guideTemplate
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GuideItemResponse> $itemResponses
 * @property-read int|null $item_responses_count
 * @property-read \App\Models\EvaluationSession $session
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuideResponse newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuideResponse newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuideResponse onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuideResponse query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuideResponse whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuideResponse whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuideResponse whereGuideTemplateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuideResponse whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuideResponse whereSessionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuideResponse whereTotalScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuideResponse whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuideResponse withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GuideResponse withoutTrashed()
 * @mixin \Eloquent
 */
class GuideResponse extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'session_id',
        'guide_template_id',
        'total_score',
    ];

    protected $casts = [
        'total_score' => 'decimal:2',
    ];

    /**
     * La sesión de evaluación a la que pertenece esta respuesta.
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(EvaluationSession::class, 'session_id');
    }

    /**
     * Plantilla usada en esta evaluación.
     */
    public function guideTemplate(): BelongsTo
    {
        return $this->belongsTo(GuideTemplate::class, 'guide_template_id');
    }

    /**
     * Respuestas de ítem asociadas a esta evaluación de plantilla.
     */
    public function itemResponses(): HasMany
    {
        return $this->hasMany(GuideItemResponse::class, 'guide_response_id');
    }

    /**
     * Calcula el score total basado en los score_obtained ya guardados
     */
    public function calculateScore(): array
    {
        $maxScaleValue = Scale::getMaxScaleValue();

        // Obtener solo los items calificables con sus respuestas
        $scorableItemsCount = $this->guideTemplate->sections()
            ->whereHas('items', function ($query) {
                $query->whereIn('type', TemplateItem::allowedScorable());
            })
            ->count();

        if ($scorableItemsCount === 0) {
            return [
                'total_score' => 0,
                'max_score' => 0,
                'overall_avg' => 0,
                'answered_avg' => null
            ];
        }

        // Sumar todos los scores obtenidos de las respuestas existentes
        $scores = $this->itemResponses()
            ->whereHas('item', function ($query) {
                $query->whereIn('type', TemplateItem::allowedScorable());
            })
            ->selectRaw('SUM(score_obtained) as total, COUNT(*) as answered_count')
            ->first();

        $totalScore = (float) ($scores->total ?? 0);
        $answeredCount = (int) ($scores->answered_count ?? 0);
        $maxScore = $scorableItemsCount * $maxScaleValue;

        return [
            'total_score' => $totalScore,
            'max_score' => $maxScore,
            'overall_avg' => $maxScore > 0 ? $totalScore / $maxScore : 0,
            'answered_avg' => $answeredCount > 0 ? $totalScore / ($answeredCount * $maxScaleValue) : null
        ];
    }

    /**
     * Calcula los promedios por sección solo con ítems calificables.
     *
     * @param  bool  $unansweredAsZero  Si true, incluye no respondidos como cero.
     * @return Collection<int, SectionAverageData>
     */
    public function getSectionAverages(bool $unansweredAsZero = true): Collection
    {
        $maxScaleValue = \App\Models\Scale::getMaxScaleValue();
        $scorableTypes = \App\Models\TemplateItem::allowedScorable();

        // Precarga con filtros y conteo de respuestas
        $this->loadMissing([
            'guideTemplate.sections.items' => function ($q) use ($scorableTypes) {
                $q->whereIn('type', $scorableTypes)
                    ->withCount([
                        'itemResponses as item_responses_count' => function ($sub) {
                            $sub->where('guide_response_id', $this->id);
                        }
                    ]);
            },
            'itemResponses.item',
        ]);

        return $this->guideTemplate->sections->map(function ($section) use ($maxScaleValue, $scorableTypes, $unansweredAsZero) {
            // Filtra ítems calificables
            $items = $section->items->filter(fn($item) => in_array($item->type, $scorableTypes));

            $planned = $items->count();
            if ($planned === 0) {
                return null;
            }

            $answered = $items->sum('item_responses_count');

            // Total de score para ítems de esta sección
            $itemIds = $items->pluck('id')->all();
            $totalScore = $this->itemResponses
                ->filter(fn($r) => in_array($r->template_item_id, $itemIds))
                ->sum('score_obtained');

            $avgAnswered = $answered > 0
                ? round(($totalScore / ($answered * $maxScaleValue)) * 100, 2)
                : null;

            $avgZeroFilled = round(($planned > 0 ? ($totalScore / ($planned * $maxScaleValue)) * 100 : 0), 2);

            return new SectionAverageData(
                section_id: $section->id,
                section_title: $section->title,
                planned: $planned,
                answered: $answered,
                avg_answered: $avgAnswered,
                avg_zero_filled: $avgZeroFilled,
                avg: $unansweredAsZero ? $avgZeroFilled : ($avgAnswered ?? 0.0),
            );
        })->filter(); // Elimina secciones sin ítems calificables
    }
}
