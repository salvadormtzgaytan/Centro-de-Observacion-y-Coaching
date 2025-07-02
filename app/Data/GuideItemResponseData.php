<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class GuideItemResponseData extends Data
{
    public function __construct(
        public readonly int     $template_item_id,
        public readonly string  $value,
        public readonly float   $score_obtained,
        public readonly ?string $observation = null,
    ) {}
}
