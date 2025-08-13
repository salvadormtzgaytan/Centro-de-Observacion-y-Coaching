<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Carbon\Carbon;

class TemplateItemData extends Data
{
    public function __construct(
        public int $id,
        public int $template_section_id,
        public string $label,
        public string $type,
        public ?string $help_text,
        public ?array $options,
        public mixed $order,
        public float $score
    ) {}
    
    public static function fromModel($model): self
    {
        return new self(
            id: $model->id,
            template_section_id: $model->template_section_id,
            label: $model->label,
            type: $model->type,
            help_text: $model->help_text,
            options: $model->options,
            order: $model->order,
            score: $model->score
        );
    }
}
