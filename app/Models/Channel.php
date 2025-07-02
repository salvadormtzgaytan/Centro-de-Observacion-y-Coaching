<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'name',
    ];

    // Plantillas asociadas a este canal
    public function guideTemplates()
    {
        return $this->hasMany(GuideTemplate::class);
    }

    // → Todas las evaluaciones (GuideResponse) hechas con plantillas de este canal
    public function responses()
    {
        return $this->hasManyThrough(
            \App\Models\GuideResponse::class,
            \App\Models\GuideTemplate::class,
            'channel_id',         // FK en guide_templates
            'guide_template_id',  // FK en guide_responses
            'id',                 // PK en channels
            'id'                  // PK en guide_templates
        );
    }

    // → (Opcional) Acceso directo a respuestas de ítems
    public function itemResponses()
    {
        return $this->hasManyThrough(
            \App\Models\GuideItemResponse::class,
            \App\Models\GuideResponse::class,
            'guide_template_id',   // FK en guide_responses
            'guide_response_id',   // FK en guide_item_responses
            'id',                  // PK en channels
            'id'                   // PK en guide_responses
        );
    }
}
