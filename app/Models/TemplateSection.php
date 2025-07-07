<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;

class TemplateSection extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'guide_template_id',
        'title',
        'order',
    ];

    /**
     * Configura el log para campos sensibles y personaliza la descripción del evento.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['guide_template_id', 'title', 'order'])
            ->useLogName('Sección de Plantilla')
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "La sección de plantilla fue {$eventName}");
    }

    /**
     * Personaliza la descripción del evento para incluir los campos modificados.
     */
    public function getDescriptionForEvent(string $eventName): string
    {
        $changes = collect($this->getDirty())->keys()->implode(', ');
        $base = "La sección de plantilla fue {$eventName}";
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
