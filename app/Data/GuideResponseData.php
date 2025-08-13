<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Carbon\Carbon;

class GuideResponseData extends Data
{
    public function __construct(
        public int $id,
        public int $session_id,
        public int $guide_template_id,
        public float $total_score
    ) {}
    
    public static function fromModel($model): self
    {
        return new self(
            id: $model->id,
            session_id: $model->session_id,
            guide_template_id: $model->guide_template_id,
            total_score: $model->total_score
        );
    }
}
