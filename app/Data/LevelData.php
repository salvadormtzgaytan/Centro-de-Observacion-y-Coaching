<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Carbon\Carbon;

class LevelData extends Data
{
    public function __construct(
        public int $id,
        public string $key,
        public string $name,
        public mixed $order
    ) {}
    
    public static function fromModel($model): self
    {
        return new self(
            id: $model->id,
            key: $model->key,
            name: $model->name,
            order: $model->order
        );
    }
}
