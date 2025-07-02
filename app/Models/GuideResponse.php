<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\GuideTemplate;
use App\Models\GuideItemResponse;

class GuideResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'guide_template_id',
        'evaluator_name',
        'participant_name',
        'date',
        'cycle',
        'total_score',
    ];

    /**
     * La plantilla a la que pertenece esta evaluación.
     */
    public function template()
    {
        return $this
            ->belongsTo(GuideTemplate::class, 'guide_template_id');
    }

    /**
     * Las respuestas individuales de ítems asociadas a esta evaluación.
     */
    public function itemResponses()
    {
        return $this
            ->hasMany(GuideItemResponse::class, 'guide_response_id');
    }
}
