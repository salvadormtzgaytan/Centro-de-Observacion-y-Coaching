<?php

declare(strict_types=1);

namespace App\Livewire\Charts;

use App\Data\Charts\RadarChartData;
use App\Models\GuideResponse;
use App\Services\GuideAnalyticsService;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Component;

class GuideSectionsRadar extends Component
{
    /**
     * Modo de cálculo:
     * - 'response': usa una respuesta de guía (guide_responses.id)
     * - 'template': agregado por plantilla (guide_template_id + filtros)
     *
     * @var 'response'|'template'
     */
    public string $mode = 'response';

    public ?int $guideResponseId = null;
    public ?int $guideTemplateId = null;

    /** Filtros válidos cuando mode=template */
    public array $filters = [
        'only_completed' => true,
        'participant_id' => null,
        'evaluator_id'   => null,
        'date_from'      => null,
        'date_to'        => null,
    ];

    /** Etiqueta del dataset en el radar */
    public string $datasetLabel = 'Promedio';

    /** Si true, promedio sobre planificados (no respondidos = 0). Si false, sólo respondidos */
    public bool $unansweredAsZero = true;

    /** Alto del canvas (px) */
    public int $height = 380;

    public function mount(): void
    {
        $this->validate([
            'mode' => ['required', Rule::in(['response', 'template'])],
            'guideResponseId' => [Rule::requiredIf(fn() => $this->mode === 'response'), 'nullable', 'integer', 'min:1'],
            'guideTemplateId' => [Rule::requiredIf(fn() => $this->mode === 'template'), 'nullable', 'integer', 'min:1'],
            'filters.only_completed' => ['boolean'],
            'filters.participant_id' => ['nullable', 'integer', 'min:1'],
            'filters.evaluator_id'   => ['nullable', 'integer', 'min:1'],
            'filters.date_from'      => ['nullable', 'date'],
            'filters.date_to'        => ['nullable', 'date', 'after_or_equal:filters.date_from'],
            'datasetLabel' => ['required', 'string', 'max:100'],
            'unansweredAsZero' => ['boolean'],
            'height' => ['integer', 'min:160', 'max:800'],
        ]);
    }

    public function render(GuideAnalyticsService $svc): View
    {
        if ($this->mode === 'response') {
            /** @var GuideResponse $gr */
            $gr = GuideResponse::query()->findOrFail($this->guideResponseId);
            $sections = $gr->getSectionAverages($this->unansweredAsZero);
        } else {
            $sections = $svc->sectionAveragesForTemplate(
                guideTemplateId: (int) $this->guideTemplateId,
                filters: $this->filters,
                unansweredAsZero: $this->unansweredAsZero
            );
        }

        /** @var RadarChartData $radar */
        $radar = $svc->radarFromSectionAverages($sections);

        return view('livewire.charts.guide-sections-radar', [
            'radar'  => $radar,
            'height' => $this->height,
            'sections' => $sections,
            'avgModeZF' => $this->unansweredAsZero,
        ]);
    }
}
