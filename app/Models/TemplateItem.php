<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;

class TemplateItem extends Model
{
    use HasFactory, LogsActivity;

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


    /**
     * Configura el log para campos sensibles y personaliza la descripción del evento.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'template_section_id',
                'label',
                'type',
                'help_text',
                'options',
                'order',
                'score',
            ])
            ->useLogName('Ítem de Plantilla')
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "El ítem de plantilla fue {$eventName}");
    }

    /**
     * Personaliza la descripción del evento para incluir los campos modificados.
     */
    public function getDescriptionForEvent(string $eventName): string
    {
        $changes = collect($this->getDirty())->keys()->implode(', ');
        $base = "El ítem de plantilla fue {$eventName}";
        return $changes ? "{$base}. Campos modificados: {$changes}" : $base;
    }

    /**
     * Agrega la IP de origen al activity log.
     */
    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->properties = $activity->properties->merge([
            'ip' => request()->ip(),
        ]);
    }

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


    public function setOptionsAttribute($value)
    {
        // $value es un array de valores seleccionados (ej: ["1", "2"])
        $scales = \App\Models\Scale::whereIn('value', $value)->get(['label', 'value']);
        $this->attributes['options'] = json_encode(
            $scales->map(fn($s) => ['label' => $s->label, 'value' => $s->value])->values()
        );
    }

    public function getOptionsAttribute($value)
    {
        // $value es el array almacenado en la base de datos
        $array = json_decode($value, true);
        // Si es un array de objetos con label y value, regresa solo los valores para el formulario
        if (is_array($array) && isset($array[0]['value'])) {
            return collect($array)->pluck('value')->map(fn($v) => (string)$v)->toArray();
        }
        // Si es un array simple, regresa tal cual
        return $array;
    }
}
