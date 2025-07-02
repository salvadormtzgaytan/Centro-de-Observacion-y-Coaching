<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Traits\HasRoles;
use Filament\Models\Contracts\FilamentUser;
use BezhanSalleh\FilamentShield\Traits\HasPanelShield;
use Kalnoy\Nestedset\NodeTrait;
use Illuminate\Support\Str;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory;
    use Notifiable;
    use HasRoles;
    use HasPanelShield;
    use NodeTrait; // Nested set behavior for jerarquía

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'parent_id', // supervisor
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Obtener iniciales del usuario.
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn($word) => Str::substr($word, 0, 1))
            ->implode('');
    }
    /**
     * Supervisor inmediato (padre en el árbol).
     */
    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Subordinados directos (hijos en el árbol).
     */
    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }
    /**
     * (Opcional) Refina quién puede acceder al panel Filament.
     */
    // public function canAccessFilament(): bool
    // {
    //     return $this->hasRole(['super_admin', 'administrador', 'auditor']);
    // }
}
