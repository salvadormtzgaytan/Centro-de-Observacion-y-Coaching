<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\Charts\RadarChartData;
use App\Data\Guide\SectionAverageData;
use App\Models\EvaluationSession;
use App\Models\GuideResponse;
use App\Models\TemplateItem as TI;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Analítica de guías a nivel de secciones.
 *
 * NOTA IMPORTANTE:
 * - Los agregados GLOBALES de la sesión ya vienen persistidos en EvaluationSession:
 *   - total_score, max_score, overall_avg (0..1), answered_avg (0..1)
 *   - overall_avg_pct, answered_avg_pct (accessors 0..100)
 * - Este servicio NO debe recalcularlos. Sólo los lee si hace falta presentarlos,
 *   y sí calcula agregados por SECCIÓN para construir gráficos (radar, etc.).
 */
final class GuideAnalyticsService
{
    /** Columna para ordenar secciones (ajústala si tienes position/sort_order). */
    private const SECTION_ORDER_COL = 'order';

    /**
     * Promedios por sección para UNA respuesta de guía.
     * Devuelve valores en misma escala que score_obtained (0..100).
     *
     * @return Collection<int, SectionAverageData>
     */
    public function sectionAveragesForGuideResponse(GuideResponse $guideResponse, bool $unansweredAsZero = false): Collection
    {
        return $guideResponse->getSectionAverages();
    }

    /**
     * Promedios por sección agregados para una plantilla (sobre múltiples sesiones).
     *
     * @return Collection<int, SectionAverageData>
     */
    public function sectionAveragesForTemplate(int $guideTemplateId, array $filters = [], bool $unansweredAsZero = false): Collection
    {
        $scoringTypes = TI::allowedScorable();
        $orderCol = self::SECTION_ORDER_COL;

        $onlyCompleted = $filters['only_completed'] ?? true;
        $participantId = $filters['participant_id'] ?? null;
        $evaluatorId = $filters['evaluator_id'] ?? null;
        $dateFrom = $filters['date_from'] ?? null;
        $dateTo = $filters['date_to'] ?? null;

        $rows = DB::table('template_sections as ts')
            ->join('template_items as ti', function ($j) use ($scoringTypes) {
                $j->on('ti.template_section_id', '=', 'ts.id')
                    ->whereIn('ti.type', $scoringTypes);
            })
            ->leftJoin('guide_item_responses as gir', 'gir.template_item_id', '=', 'ti.id')
            ->leftJoin('guide_responses as gr', function ($j) use ($guideTemplateId) {
                $j->on('gr.id', '=', 'gir.guide_response_id')
                    ->where('gr.guide_template_id', '=', $guideTemplateId);
            })
            ->leftJoin('evaluation_sessions as es', 'es.id', '=', 'gr.session_id')
            ->where('ts.guide_template_id', '=', $guideTemplateId)
            ->when($onlyCompleted, fn ($q) => $q->where('es.status', EvaluationSession::STATUS_COMPLETED))
            ->when($participantId, fn ($q, $v) => $q->where('es.participant_id', $v))
            ->when($evaluatorId, fn ($q, $v) => $q->where('es.evaluator_id', $v))
            ->when($dateFrom, fn ($q, $v) => $q->whereDate('es.date', '>=', $v))
            ->when($dateTo, fn ($q, $v) => $q->whereDate('es.date', '<=', $v))
            ->groupBy('ts.id', 'ts.title')
            ->orderBy("ts.$orderCol")
            ->selectRaw('ts.id, ts.title')
            ->selectRaw('COUNT(DISTINCT ti.id)                 as planned')
            ->selectRaw('COUNT(gir.id)                         as answered')
            ->selectRaw('AVG(gir.score_obtained)               as avg_answered')
            ->selectRaw('AVG(COALESCE(gir.score_obtained, 0))  as avg_zero_filled')
            ->get();

        return $rows->map(function ($r) use ($unansweredAsZero) {
            $avgAnswered = $r->avg_answered !== null ? round((float) $r->avg_answered, 2) : null; // 0..100 | null
            $avgZeroFilled = round((float) $r->avg_zero_filled, 2);                                  // 0..100

            return new SectionAverageData(
                section_id: (int) $r->id,
                section_title: (string) $r->title,
                planned: (int) $r->planned,
                answered: (int) $r->answered,
                avg_answered: $avgAnswered,
                avg_zero_filled: $avgZeroFilled,
                avg: $unansweredAsZero ? $avgZeroFilled : $avgAnswered,
            );
        });
    }

    /**
     * Crea un dataset para Radar a partir de los promedios por sección.
     * Asume que las medias vienen en 0..100 (score_obtained ya está así).
     */
    public function radarFromSectionAverages(
        Collection $sections,
        string $datasetLabel = 'Promedio (%)'
    ): RadarChartData {
        $labels = $sections->pluck('section_title')->values()->all();

        $data = $sections->pluck('avg')
            ->map(fn ($v) => $v === null ? 0.0 : round((float) $v, 2)) // ya en 0..100
            ->values()
            ->all();

        return new RadarChartData(
            labels: $labels,
            datasets: [
                new \App\Data\Charts\RadarDatasetData(
                    label: $datasetLabel,
                    data: $data,
                ),
            ],
        );
    }

    /**
     * Wrapper de conveniencia: lee los agregados globales PERSISTIDOS de la sesión.
     * Retorna ambos formatos: 0..1 y 0..100 para consumo rápido en UI/reporte.
     */
    public function sessionGlobalMetrics(EvaluationSession $session): array
    {
        return [
            // crudos (0..1)
            'answered_avg' => $session->answered_avg,
            'overall_avg' => $session->overall_avg,
            'total_score' => $session->total_score,
            'max_score' => $session->max_score,

            // presentables (0..100) vía accessors del modelo
            'answered_avg_pct' => $session->answered_avg_pct, // 0..100
            'overall_avg_pct' => $session->overall_avg_pct,  // 0..100
        ];
    }

    public function sectionAveragesForSession(EvaluationSession $session, bool $unansweredAsZero = false): Collection
    {
        $scoringTypes = TI::allowedScorable();

        $rows = DB::table('template_sections as ts')
            ->join('template_items as ti', function ($j) use ($scoringTypes) {
                $j->on('ti.template_section_id', '=', 'ts.id')
                    ->whereIn('ti.type', $scoringTypes);
            })
            ->join('guide_responses as gr', 'gr.guide_template_id', '=', 'ts.guide_template_id')
            ->leftJoin('guide_item_responses as gir', function ($j) {
                $j->on('gir.template_item_id', '=', 'ti.id')
                    ->on('gir.guide_response_id', '=', 'gr.id');
            })
            ->where('gr.session_id', $session->id)
            ->groupBy('ts.id', 'ts.title', 'ts.order')
            ->orderBy('ts.order')
            ->selectRaw('ts.id, ts.title')
            ->selectRaw('COUNT(DISTINCT ti.id)                 as planned')
            ->selectRaw('COUNT(gir.id)                         as answered')
            ->selectRaw('AVG(gir.score_obtained)               as avg_answered')
            ->selectRaw('AVG(COALESCE(gir.score_obtained, 0))  as avg_zero_filled')
            ->get();

        return $rows->map(function ($r) use ($unansweredAsZero) {
            $avgAnswered = $r->avg_answered !== null ? round((float) $r->avg_answered, 2) : null;
            $avgZeroFilled = round((float) $r->avg_zero_filled, 2);

            return new SectionAverageData(
                section_id: (int) $r->id,
                section_title: (string) $r->title,
                planned: (int) $r->planned,
                answered: (int) $r->answered,
                avg_answered: $avgAnswered,
                avg_zero_filled: $avgZeroFilled,
                avg: $unansweredAsZero ? $avgZeroFilled : $avgAnswered,
            );
        });
    }
}
