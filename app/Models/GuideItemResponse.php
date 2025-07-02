<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuideItemResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'guide_response_id',
        'template_item_id',
        'value',
        'score_obtained',
        'observation',
    ];

    public function response()
    {
        return $this->belongsTo(GuideResponse::class, 'guide_response_id');
    }

    public function item()
    {
        return $this->belongsTo(TemplateItem::class, 'template_item_id');
    }
}
