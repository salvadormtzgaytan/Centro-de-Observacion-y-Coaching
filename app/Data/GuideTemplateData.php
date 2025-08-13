<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Carbon\Carbon;

class GuideTemplateData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public int $division_id,
        public int $level_id,
        public int $channel_id,
        public string $status
    ) {}
    
    public static function fromModel($model): self
    {
        return new self(
            id: $model->id,
            name: $model->name,
            division_id: $model->division_id,
            level_id: $model->level_id,
            channel_id: $model->channel_id,
            status: $model->status
        );
    }
}
