<?php

declare(strict_types=1);

namespace App\Data\Charts;

use Spatie\LaravelData\Data;

/**
 * Estructura mínima para un radar: labels + datasets.
 * - labels: títulos de las secciones en orden.
 * - datasets: uno o más conjuntos (ej. "Promedio sesión", "Promedio histórico").
 */
final class RadarChartData extends Data
{
    /** @param list<string> $labels @param list<RadarDatasetData> $datasets */
    public function __construct(
        public array $labels,
        public array $datasets,
    ) {}
}
