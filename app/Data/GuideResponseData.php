<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class GuideResponseData extends Data
{
    public function __construct(
        public readonly int                           $guide_template_id,
        public readonly ?string                       $evaluator_name = null,
        public readonly ?string                       $participant_name = null,
        public readonly ?string                       $date = null,
        public readonly ?string                       $cycle = null,
        /** @var DataCollection<GuideItemResponseData> */
        public readonly DataCollection                $item_responses,
        public readonly float                         $total_score = 0.0,
    ) {}
}
