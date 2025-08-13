<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Carbon\Carbon;

class ScaleData extends Data
{
    public function __construct(
        public int $id,
        public string $label,
        public float $value,
        public mixed $order
    ) {}
    
    public static function fromModel($model): self
    {
        return new self(
            id: $model->id,
            label: $model->label,
            value: $model->value,
            order: $model->order
        );
    }
}
