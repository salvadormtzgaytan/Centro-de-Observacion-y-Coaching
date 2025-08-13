<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cycle extends Model
{
    use HasFactory;
    // use SoftDeletes;

    protected $fillable = [
        'code',
        'key',          // p.ej. "FY2025-Q1"
        'label',        // p.ej. "Q1 2025"
        'fiscal_year',  // int
        'quarter',      // 1..4
        'starts_at',    // date
        'ends_at',      // date
        'is_open',      // bool
        'division_id',  // null = global
    ];

    protected $casts = [
        'fiscal_year' => 'integer',
        'quarter'     => 'integer',
        'is_open'     => 'boolean',
        'starts_at'   => 'date',
        'ends_at'     => 'date',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relaciones
    |--------------------------------------------------------------------------
    */

    public function division()
    {
        return $this->belongsTo(\App\Models\Division::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors / Helpers
    |--------------------------------------------------------------------------
    */

    // App\Models\Cycle.php
    public function getLabelWithRangeAttribute(): string
    {
        $label = $this->label; // "Q1 2025"
        $from  = $this->starts_at ? $this->starts_at->isoFormat('DD MMM') : null;
        $to    = $this->ends_at   ? $this->ends_at->isoFormat('DD MMM')   : null;

        if ($from && $to) {
            return "{$label} ({$from} – {$to})";
        }

        // Si faltan fechas, solo muestra el label
        return $label;
    }


    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeOpen($q)
    {
        return $q->where('is_open', true);
    }

    public function scopeYear($q, int $fy)
    {
        return $q->where('fiscal_year', $fy);
    }

    public function scopeQuarter($q, int $quarter)
    {
        return $q->where('quarter', $quarter);
    }

    public function scopeForDivision($q, ?int $divisionId)
    {
        // Si $divisionId es null, trae globales; si no, filtra por división
        return $divisionId === null
            ? $q->whereNull('division_id')
            : $q->where('division_id', $divisionId);
    }

    /*
    |--------------------------------------------------------------------------
    | Utilidades
    |--------------------------------------------------------------------------
    */

    public static function makeKey(int $fy, int $q): string
    {
        return "FY{$fy}-Q{$q}";
    }

    public static function makeLabel(int $fy, int $q): string
    {
        return "Q{$q} {$fy}";
    }
}
