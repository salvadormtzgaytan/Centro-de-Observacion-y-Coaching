<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class TemplateItemData extends Data
{
    public function __construct(
        public readonly string      $label,
        public readonly string      $type,
        public readonly ?string     $help_text = null,
        public readonly array       $options  = [],
        public readonly int         $order    = 0,
        public readonly float       $score    = 0.0,
    ) {}
}
