<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Carbon\Carbon;

class EvaluationSessionData extends Data
{
    public function __construct(
        public int $id,
        public int $evaluator_id,
        public int $participant_id,
        public ?\Carbon\Carbon $date,
        public ?string $cycle,
        public string $status,
        public ?string $pdf_path,
        public float $total_score
    ) {}
    
    public static function fromModel($model): self
    {
        return new self(
            id: $model->id,
            evaluator_id: $model->evaluator_id,
            participant_id: $model->participant_id,
            date: $model->date,
            cycle: $model->cycle,
            status: $model->status,
            pdf_path: $model->pdf_path,
            total_score: $model->total_score
        );
    }
}
