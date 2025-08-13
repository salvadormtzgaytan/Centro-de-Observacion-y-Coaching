<?php

namespace App\Utils;

use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;

class ActivityLogHelper
{
    public static function getLogOptions(array $fields, string $logName, string $entityLabel): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($fields)
            ->useLogName($logName)
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "{$entityLabel} fue {$eventName}");
    }

    public static function getDescriptionForEvent(string $eventName, string $entityLabel, array $dirtyFields): string
    {
        $changes = collect($dirtyFields)->implode(', ');
        $base = "{$entityLabel} fue {$eventName}";
        return $changes ? "{$base}. Campos modificados: {$changes}" : $base;
    }

    public static function tapActivity(Activity $activity)
    {
        $activity->properties = $activity->properties->merge([
            'ip' => request()->ip(),
        ]);
    }
}
