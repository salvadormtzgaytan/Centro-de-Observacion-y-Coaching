<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class TemplateSectionData extends Data
{
    public function __construct(
        public readonly string                $title,
        public readonly int                   $order,
        public readonly DataCollection        $items,    // DataCollection<TemplateItemData>
    ) {}
}
