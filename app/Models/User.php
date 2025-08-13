<?php

namespace App\Models;

use BezhanSalleh\FilamentShield\Traits\HasPanelShield;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Kalnoy\Nestedset\NodeTrait;
use Spatie\Permission\Traits\HasRoles;

/**
 * App\Models\User
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string|null $profile_photo_path
 * @property int|null $parent_id
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property-read string|null $profile_photo_url
 *
 * Relaciones / colecciones:
 * @property-read \App\Models\User|null $parent
 * @property-read \Illuminate\Database\Eloquent\Collection<int,\App\Models\User> $children
 * @property-read \Illuminate\Database\Eloquent\Collection<int,\App\Models\EvaluationSession> $evaluationSessions
 * @property-read \Illuminate\Database\Eloquent\Collection<int,\App\Models\EvaluationSession> $receivedEvaluations
 * @property-read \Illuminate\Database\Eloquent\Collection<int,\App\Models\GuideResponse> $guideResponses
 *
 * Notificaciones (trait Notifiable):
 * @property-read \Illuminate\Database\Eloquent\Collection<int,\Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read \Illuminate\Database\Eloquent\Collection<int,\Illuminate\Notifications\DatabaseNotification> $unreadNotifications
 *
 * Helpers de Spatie Permission:
 *
 * @method bool hasRole(string|array $roles)
 * @method bool hasAnyRole(string|array $roles)
 *
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $_lft
 * @property int $_rgt
 * @property-read int|null $children_count
 * @property-read int|null $evaluation_sessions_count
 * @property-read int|null $guide_responses_count
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read int|null $received_evaluations_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Role> $roles
 * @property-read int|null $roles_count
 *
 * @method static \Kalnoy\Nestedset\Collection<int, static> all($columns = ['*'])
 * @method static \Kalnoy\Nestedset\QueryBuilder<static>|User ancestorsAndSelf($id, array $columns = [])
 * @method static \Kalnoy\Nestedset\QueryBuilder<static>|User ancestorsOf($id, array $columns = [])
 * @method static \Kalnoy\Nestedset\QueryBuilder<static>|User applyNestedSetScope(?string $table = null)
 * @method static \Kalnoy\Nestedset\QueryBuilder<static>|User countErrors()
 * @method static \Kalnoy\Nestedset\QueryBuilder<static>|User d()
 * @method static \Kalnoy\Nestedset\QueryBuilder<static>|User defaultOrder(string $dir = 'asc')
 * @method static \Kalnoy\Nestedset\QueryBuilder<static>|User descendantsAndSelf($id, array $columns = [])
 * @method static \Kalnoy\Nestedset\QueryBuilder<static>|User descendantsOf($id, array $columns = [], $andSelf = false)
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Kalnoy\Nestedset\QueryBuilder<static>|User fixSubtree($root)
 * @method static \Kalnoy\Nestedset\QueryBuilder<static>|User fixTree($root = null)
 * @method static \Kalnoy\Nestedset\Collection<int, static> get($columns = ['*'])
 * @method static \Kalnoy\Nestedset\QueryBuilder<static>|User getNodeData($id, $required = false)
 * @method static \Kalnoy\Nestedset\QueryBuilder<static>|User getPlainNodeData($id, $required = false)
 * @method static \Kalnoy\Nestedset\QueryBuilder<static>|User getTotalErrors()
 * @method static \Kalnoy\Nestedset\QueryBuilder<static>|User hasChildren()
 * @method static \Kalnoy\Nestedset\QueryBuilder<static>|User hasParent()
 * @method static \Kalnoy\Nestedset\QueryBuilder<static>|User isBroken()
 * @method static \Kalnoy\Nestedset\QueryBuilder<static>|User leaves(array $columns = [])
 * @method static \Kalnoy\Nestedset\QueryBuilder<static>|User makeGap(int $cut, int $height)
 * @method static \Kalnoy\Nestedset\QueryBuilder<static>|User moveNode($key, $position)
 * @method static \Kalnoy\Nestedset\QueryBuilder<static>|User newModelQuery()
 * @method static \Kalnoy\Nestedset\QueryBuilder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User onlyTrashed()
 * @method static \Kalnoy\Nestedset\QueryBuilder<static>|User orWhereAncestorOf(bool $id, bool $andSelf = false)
 * @method static \Kalnoy\Nestedset\QueryBuilder<static>|User orWhereDescendantOf($id)
 * @method static \Kalnoy\Nestedset\QueryBuilder<static>|User orWhereNodeBetween($values)
 * @method static \Kalnoy\Nestedset\QueryBuilder<static>|User orWhereNotDescendantOf($id)
 * @method static \Kalnoy\Nestedset\QueryBuilder<static>|User permission($permissions, $without = false)
 * @method static \Kalnoy\Nestedset\QueryBuilder<static>|User query()
 * @method static \Kalnoy\Nestedset\QueryBuilder<static>|User rebuildSubtree($root, array $data, $delete = false)
 * @method static \Kalnoy\Nestedset\QueryBuilder<static>|User rebuildTree(array $data, $delete = false, $root = null)
 * @method static \Kalnoy\Nestedset\QueryBuilder<static>|User reversed()
 * @method static \Kalnoy\Nestedset\QueryBuilder<static>|User role($roles, $guard = null, $without = false)
 * @method static \Kalnoy\Nestedset\QueryBuilder<static>|User root(array $columns = [])
 * @method static \Kalnoy\Nestedset\QueryBuilder<static>|User whereAncestorOf($id, $andSelf = false, $boolean = 'and')
 * @method static \Kalnoy\Nestedset\QueryBuilder<static>|User whereAncestorOrSelf($id)
 * @method static \Kalnoy\Nestedset\QueryBuilder<static>|User whereCreatedAt($value)
 * @method static \Kalnoy\Nestedset\QueryBuilder<static>|User whereDeletedAt($value)
 * @method static \Kalnoy\Nestedset\QueryBuilder<static>|User whereDescendantOf($id, $boolean = 'and', $not = false, $andSelf = false)
 * @method static \Kalnoy\Nestedset\QueryBuilder<static>|User whereDescendantOrSelf(string $id, string $boolean = 'and', string $not = false)
 * @method static \Kalnoy\Nestedset\QueryBuilder<static>|User whereEmail($value)
 * @method static \Kalnoy\Nestedset\QueryBuilder<static>|User whereEmailVerifiedAt($value)
 * @method static \Kalnoy\Nestedset\QueryBuilder<static>|User whereId($value)
 * @method static \Kalnoy\Nestedset\QueryBuilder<static>|User whereIsAfter($id, $boolean = 'and')
 * @method static \Kalnoy\Nestedset\QueryBuilder<static>|User whereIsBefore($id, $boolean = 'and')
 * @method static \Kalnoy\Nestedset\QueryBuilder<static>|User whereIsLeaf()
 * @method static \Kalnoy\Nestedset\QueryBuilder<static>|User whereIsRoot()
 * @method static \Kalnoy\Nestedset\QueryBuilder<static>|User whereLft($value)
 * @method static \Kalnoy\Nestedset\QueryBuilder<static>|User whereName($value)
 * @method static \Kalnoy\Nestedset\QueryBuilder<static>|User whereNodeBetween($values, $boolean = 'and', $not = false, $query = null)
 * @method static \Kalnoy\Nestedset\QueryBuilder<static>|User whereNotDescendantOf($id)
 * @method static \Kalnoy\Nestedset\QueryBuilder<static>|User whereParentId($value)
 * @method static \Kalnoy\Nestedset\QueryBuilder<static>|User wherePassword($value)
 * @method static \Kalnoy\Nestedset\QueryBuilder<static>|User whereProfilePhotoPath($value)
 * @method static \Kalnoy\Nestedset\QueryBuilder<static>|User whereRememberToken($value)
 * @method static \Kalnoy\Nestedset\QueryBuilder<static>|User whereRgt($value)
 * @method static \Kalnoy\Nestedset\QueryBuilder<static>|User whereUpdatedAt($value)
 * @method static \Kalnoy\Nestedset\QueryBuilder<static>|User withDepth(string $as = 'depth')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withTrashed(bool $withTrashed = true)
 * @method static \Kalnoy\Nestedset\QueryBuilder<static>|User withoutPermission($permissions)
 * @method static \Kalnoy\Nestedset\QueryBuilder<static>|User withoutRole($roles, $guard = null)
 * @method static \Kalnoy\Nestedset\QueryBuilder<static>|User withoutRoot()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutTrashed()
 *
 * @mixin \Eloquent
 */
class User extends Authenticatable implements FilamentUser, HasAvatar
{
    use HasFactory;
    use HasPanelShield;
    use HasRoles;
    use NodeTrait;
    use Notifiable;

    // → habilita notifications(), unreadNotifications, notify()
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'parent_id',
        'profile_photo_path',
        'is_active', // Agregado
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @var array<string,string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'last_login_at' => 'datetime', // Agregado
    ];

    /**
     * @var list<string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * ¿Puede acceder al panel de Filament?
     */
    // public function canAccessPanel(\Filament\Panel $panel): bool
    // {
    //     return $this->hasRole('admin') || $this->hasRole('super_admin');
    // }

    /**
     * Iniciales del usuario (máx 2 letras).
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    /**
     * Supervisor inmediato.
     *
     * @return BelongsTo<User,User>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Subordinados directos.
     *
     * @return HasMany<User>
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * Sesiones de evaluación creadas por este usuario (como evaluador).
     *
     * @return HasMany<EvaluationSession>
     */
    public function evaluationSessions(): HasMany
    {
        return $this->hasMany(EvaluationSession::class, 'evaluator_id');
    }

    /**
     * Evaluaciones donde este usuario fue participante (coachee).
     *
     * @return HasMany<EvaluationSession>
     */
    public function receivedEvaluations(): HasMany
    {
        return $this->hasMany(EvaluationSession::class, 'participant_id');
    }

    /**
     * Respuestas/guías capturadas por este usuario (si aplica).
     *
     * @return HasMany<GuideResponse>
     */
    public function guideResponses(): HasMany
    {
        return $this->hasMany(GuideResponse::class, 'evaluator_id');
    }

    /**
     * URL pública de la foto de perfil o null si no hay.
     */
    public function getProfilePhotoUrlAttribute(): ?string
    {
        if (! $this->profile_photo_path) {
            return null;
        }

        return Storage::url($this->profile_photo_path);
    }

    /**
     * Avatar para Filament.
     */
    public function getFilamentAvatarUrl(): ?string
    {
        if (! $this->profile_photo_path) {
            return null;
        }

        return Storage::url($this->profile_photo_path);
    }
}