<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuideTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'division_id',
        'level_id',
        'channel_id',
        'status',
    ];

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
