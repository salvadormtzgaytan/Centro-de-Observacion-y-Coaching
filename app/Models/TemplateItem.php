<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemplateItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'template_section_id',
        'label',
        'type',
        'help_text',
        'options',
        'order',
        'score',
    ];

    protected $casts = [
        'options' => 'array',
        'score'   => 'decimal:2',
    ];

    public function section()
    {
        return $this->belongsTo(TemplateSection::class, 'template_section_id');
    }

    /**
     * Respuestas que se guardan para este ítem
     */
    public function itemResponses()
    {
        return $this->hasMany(GuideItemResponse::class, 'template_item_id');
    }
}
