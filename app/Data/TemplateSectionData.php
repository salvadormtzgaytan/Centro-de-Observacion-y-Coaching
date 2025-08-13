<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Carbon\Carbon;

class TemplateSectionData extends Data
{
    public function __construct(
        public int $id,
        public int $guide_template_id,
        public string $title,
        public mixed $order
    ) {}
    
    public static function fromModel($model): self
    {
        return new self(
            id: $model->id,
            guide_template_id: $model->guide_template_id,
            title: $model->title,
            order: $model->order
        );
    }
}
