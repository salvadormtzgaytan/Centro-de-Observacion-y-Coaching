<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;

class GuideTemplate extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'division_id',
        'level_id',
        'channel_id',
        'status',
    ];

    /**
     * Configura el log para campos sensibles y personaliza la descripción del evento.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'division_id', 'level_id', 'channel_id', 'status'])
            ->useLogName('Plantilla de Guía')
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "El recurso Plantilla de Guía fue {$eventName}");
    }

    /**
     * Personaliza la descripción del evento para incluir los campos modificados.
     */
    public function getDescriptionForEvent(string $eventName): string
    {
        $changes = collect($this->getDirty())->keys()->implode(', ');
        $base = "El recurso Plantilla de Guía fue {$eventName}";
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

    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    public function level()
    {
        return $this->belongsTo(Level::class);
    }

    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }

    public function sections()
    {
        return $this->hasMany(TemplateSection::class)
            ->orderBy('order');
    }
    public function responses()
    {
        return $this->hasMany(GuideResponse::class, 'guide_template_id');
    }
}
