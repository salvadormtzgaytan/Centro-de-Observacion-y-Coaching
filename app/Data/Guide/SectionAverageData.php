<?php

declare(strict_types=1);

namespace App\Data\Guide;

use Spatie\LaravelData\Data;

final class SectionAverageData extends Data
{
    public function __construct(
        public int $section_id,
        public string $section_title,
        public int $planned,
        public int $answered,
        public ?float $avg_answered,   // null si no hay respondidos
        public float $avg_zero_filled, // promedio contando no respondidos como 0
        public ?float $avg,            // la métrica elegida para UI (answered o zero-filled)
    ) {}
}
