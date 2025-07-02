<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemplateSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'guide_template_id',
        'title',
        'order',
    ];

    public function guideTemplate()
    {
        return $this->belongsTo(GuideTemplate::class);
    }

    public function items()
    {
        return $this->hasMany(TemplateItem::class)
                    ->orderBy('order');
    }
}
