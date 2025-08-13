<?php

declare(strict_types=1);

namespace App\Data\Charts;

use Spatie\LaravelData\Data;

/**
 * Dataset para un Radar (Chart.js/Recharts).
 * data: valores alineados a labels del RadarChartData.
 */
final class RadarDatasetData extends Data
{
    public function __construct(
        public string $label,
        /** @var list<float|null> */
        public array $data,
    ) {}
}
