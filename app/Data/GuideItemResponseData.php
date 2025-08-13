<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Carbon\Carbon;

class GuideItemResponseData extends Data
{
    public function __construct(
        public int $id,
        public int $guide_response_id,
        public int $template_item_id,
        public ?array $answer,
        public float $score_obtained
    ) {}
    
    public static function fromModel($model): self
    {
        return new self(
            id: $model->id,
            guide_response_id: $model->guide_response_id,
            template_item_id: $model->template_item_id,
            answer: $model->answer,
            score_obtained: $model->score_obtained
        );
    }
}
