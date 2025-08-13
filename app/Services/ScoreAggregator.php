<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\EvaluationSession;
use App\Models\GuideResponse;
use App\Models\Scale;
use App\Models\TemplateItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Servicio para el cálculo y gestión de puntuaciones de evaluaciones
 *
 * Esta clase maneja todos los cálculos relacionados con puntuaciones en el sistema,
 * incluyendo el soporte para registros eliminados lógicamente (soft deletes).
 * Proporciona métodos para recalcular puntuaciones tanto para respuestas individuales
 * como para sesiones completas de evaluación.
 *
 * @version 2.1.0
 *
 * @since 2023-06-15
 */
final class ScoreAggregator
{
    /**
     * @var float|null Valor caché del máximo de la escala para optimización
     */
    private ?float $maxScaleValue = null;

    /**
     * Obtiene el valor máximo configurado en la escala del sistema
     *
     * Este valor se utiliza como base para todos los cálculos de normalización
     * y representa la puntuación máxima que puede obtener un ítem individual.
     * El valor se cachea internamente para mejorar el rendimiento.
     *
     * @return float Valor máximo de la escala definida en el sistema
     *
     * @throws \RuntimeException Si no se puede determinar el valor máximo
     */
    private function getMaxScaleValue(): float
    {
        if ($this->maxScaleValue === null) {
            $this->maxScaleValue = Scale::getMaxScaleValue();

            if ($this->maxScaleValue <= 0) {
                throw new \RuntimeException('El valor máximo de la escala debe ser mayor que cero');
            }
        }

        return $this->maxScaleValue;
    }

    /**
     * Recalcula y actualiza los puntajes para una sesión de evaluación completa
     *
     * Este método:
     * 1. Considera todos los registros incluyendo los eliminados lógicamente
     * 2. Calcula los puntajes basados solo en preguntas y respuestas disponibles
     * 3. Actualiza las métricas de la sesión con valores precisos
     *
     * @param  EvaluationSession  $session  La sesión de evaluación a recalcular
     *
     * @throws \RuntimeException Si ocurre un error en los cálculos
     *
     * @example
     * $scoreAggregator->recalcSession($evaluationSession);
     */
    public function recalcSession(EvaluationSession $session): void
    {
        try {
            $maxScaleValue = $this->getMaxScaleValue();

            // Obtener IDs de respuestas incluyendo soft deleted
            $guideResponseIds = $session->guideResponses()->withTrashed()->pluck('id');

            if ($guideResponseIds->isEmpty()) {
                $this->resetSessionScores($session);

                return;
            }

            // 1. Obtener respuestas reales (excluyendo eliminadas)
            $realResponses = $this->getRealResponses($guideResponseIds);

            // 2. Contar preguntas disponibles (no eliminadas)
            $availableQuestions = $this->countAvailableQuestions($guideResponseIds);

            // 3. Calcular valores basados en datos reales
            $calculations = $this->calculateSessionValues($realResponses, $availableQuestions, $maxScaleValue);

            // 4. Actualizar la sesión
            $this->updateSessionScores($session, $calculations);
        } catch (\Exception $e) {
            throw new \RuntimeException('Error al recalcular la sesión: '.$e->getMessage());
        }
    }

    /**
     * Recalcula y actualiza los puntajes para una respuesta de guía individual
     *
     * @param  GuideResponse  $guideResponse  La respuesta a recalcular
     *
     * @throws \RuntimeException Si ocurre un error en los cálculos
     */
    public function recalcGuideResponse(GuideResponse $guideResponse): void
    {
        try {
            $maxScaleValue = $this->getMaxScaleValue();

            // Contar preguntas disponibles (no eliminadas)
            $availableQuestions = DB::table('template_items as ti')
                ->join('template_sections as ts', 'ts.id', '=', 'ti.template_section_id')
                ->where('ts.guide_template_id', $guideResponse->guide_template_id)
                ->whereIn('ti.type', TemplateItem::allowedScorable())
                ->whereNull('ti.deleted_at')
                ->whereNull('ts.deleted_at')
                ->count();

            // Calcular puntajes obtenidos guide_item_responses no maneja softdeletes
            $sumObtained = (float) DB::table('guide_item_responses as gir')
                ->join('template_items as ti', function ($join) {
                    $join->on('ti.id', '=', 'gir.template_item_id')
                        ->whereNull('ti.deleted_at');
                })
                ->where('gir.guide_response_id', $guideResponse->id)
                ->whereIn('ti.type', TemplateItem::allowedScorable())
                // ->whereNull('gir.deleted_at')
                ->sum('gir.score_obtained');

            $maxPossible = $availableQuestions * $maxScaleValue;
            $normalizedScore = $maxPossible > 0 ? ($sumObtained / $maxPossible) : 0;

            $guideResponse->forceFill([
                'total_score' => round($normalizedScore, 4),
            ])->saveQuietly();
        } catch (\Exception $e) {
            throw new \RuntimeException('Error al recalcular la respuesta: '.$e->getMessage());
        }
    }

    /**
     * Obtiene las respuestas reales excluyendo las eliminadas
     *
     * @param  Collection  $guideResponseIds  IDs de las respuestas de guía
     * @return \Illuminate\Support\Collection Colección de respuestas
     */
    private function getRealResponses(Collection $guideResponseIds): Collection
    {
        return DB::table('guide_item_responses as gir')
            ->join('template_items as ti', function ($join) {
                $join->on('ti.id', '=', 'gir.template_item_id')
                    ->whereNull('ti.deleted_at');
            })
            ->whereIn('gir.guide_response_id', $guideResponseIds)
            ->whereIn('ti.type', TemplateItem::allowedScorable())
            // ->whereNull('gir.deleted_at')
            ->get(['gir.score_obtained', 'gir.template_item_id']);
    }

    /**
     * Cuenta las preguntas disponibles (no eliminadas)
     *
     * @param  Collection  $guideResponseIds  IDs de las respuestas de guía
     * @return int Número de preguntas disponibles
     */
    private function countAvailableQuestions(Collection $guideResponseIds): int
    {
        return DB::table('template_items as ti')
            ->join('template_sections as ts', 'ts.id', '=', 'ti.template_section_id')
            ->join('guide_responses as gr', 'gr.guide_template_id', '=', 'ts.guide_template_id')
            ->whereIn('gr.id', $guideResponseIds)
            ->whereIn('ti.type', TemplateItem::allowedScorable())
            ->whereNull('ti.deleted_at')
            ->whereNull('ts.deleted_at')
            ->distinct('ti.id')
            ->count('ti.id');
    }

    /**
     * Calcula los valores para la sesión
     *
     * @param  Collection  $realResponses  Respuestas reales
     * @param  int  $availableQuestions  Preguntas disponibles
     * @param  float  $maxScaleValue  Valor máximo de escala
     * @return array Resultados del cálculo
     */
    private function calculateSessionValues(Collection $realResponses, int $availableQuestions, float $maxScaleValue): array
    {
        $sumObtained = $realResponses->sum('score_obtained');
        $answeredCount = $realResponses->unique('template_item_id')->count();
        $answeredMax = $answeredCount * $maxScaleValue;
        $totalMaxScore = $availableQuestions * $maxScaleValue;

        return [
            'sumObtained' => $sumObtained,
            'answeredCount' => $answeredCount,
            'answeredMax' => $answeredMax,
            'totalMaxScore' => $totalMaxScore,
            'overallAvg' => $totalMaxScore > 0 ? ($sumObtained / $totalMaxScore) : 0,
            'currentAvg' => $answeredMax > 0 ? ($sumObtained / $answeredMax) : 0,
        ];
    }

    /**
     * Actualiza los puntajes de la sesión
     *
     * @param  EvaluationSession  $session  La sesión a actualizar
     * @param  array  $calculations  Resultados del cálculo
     */
    private function updateSessionScores(EvaluationSession $session, array $calculations): void
    {
        $session->forceFill([
            'total_score' => round($calculations['sumObtained'], 2),
            'max_score' => round($calculations['totalMaxScore'], 2),
            'overall_avg' => round($calculations['overallAvg'], 4),
            'answered_avg' => round($calculations['currentAvg'], 4),
        ])->saveQuietly();
    }

    /**
     * Resetea los scores de una sesión a cero
     *
     * @param  EvaluationSession  $session  La sesión a resetear
     */
    private function resetSessionScores(EvaluationSession $session): void
    {
        $session->forceFill([
            'total_score' => 0.0,
            'max_score' => 0.0,
            'overall_avg' => 0.0,
            'answered_avg' => 0.0,
        ])->saveQuietly();
    }
}
