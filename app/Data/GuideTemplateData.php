<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class GuideTemplateData extends Data
{
    public function __construct(
        public readonly string                $name,
        public readonly ?int                  $division_id,
        public readonly ?int                  $level_id,
        public readonly ?int                  $channel_id,
        public readonly string                $status,
        public readonly DataCollection        $sections, // DataCollection<TemplateSectionData>
    ) {}
}
