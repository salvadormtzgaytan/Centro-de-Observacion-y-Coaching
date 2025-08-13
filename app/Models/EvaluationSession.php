<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * -----------------------------------------------------------------------------
 *  Modelo: EvaluationSession
 * -----------------------------------------------------------------------------
 *  Propósito
 *  ---------
 *  Representa una “sesión de evaluación/observación” entre un coach (evaluator)
 *  y un coachee (participant). Es la entidad raíz que:
 *    - Orquesta el llenado de respuestas (a través de GuideResponse → Template).
 *    - Consolida métricas de puntaje (total_score, max_score, overall_avg, answered_avg).
 *    - Controla el flujo de estado y la firma digital (coach y coachee).
 *
 *  Ciclo de vida de estado (dominio)
 *  ---------------------------------
 *    draft        → Sesión creada, sin trabajo iniciado. (No visible para coachee)
 *    in_progress  → El coach comenzó a llenar respuestas (primer guardado).
 *    pending      → (Opcional según negocio) Sesión planificada, aún sin actividad.
 *    signed       → El coach ya firmó; queda pendiente la firma del coachee.
 *    completed    → Ambas firmas registradas (coach + coachee).
 *    cancelled    → Sesión anulada por negocio.
 *
 *  Reglas de firma (resumen)
 *  -------------------------
 *  - El coachee SOLO puede firmar cuando la sesión está en estado SIGNED (es decir,
 *    el coach ya firmó). Ver: needsSignatureFrom($userId, 'coachee').
 *  - El coach puede firmar mientras la sesión NO esté completed/cancelled y aún esté
 *    en pending o in_progress (y obviamente no haya firmado él). Ver: needsSignatureFrom().
 *  - COMPLETED y CANCELLED cierran la ventana de firma para todos.
 *
 *  Métricas de puntaje (persistidas)
 *  ---------------------------------
 *  - total_score  : suma real obtenida (ponderada por escala) en TODA la sesión.
 *  - max_score    : máximo posible (suma de máximos por ítem calificable).
 *  - overall_avg  : total_score / max_score      → [0..1] (incluye no respondidas = 0).
 *  - answered_avg : total_score / max_respondidas→ [0..1] (solo ítems respondidos).
 *  - overall_avg_pct / answered_avg_pct: accessors en [0..100] para UI/reportes.
 *
 *  ¿Quién mantiene estas métricas?
 *  -------------------------------
 *  Los observers + App\Services\ScoreAggregator recalculan y persisten
 *  *después* de crear/actualizar/borrar/restaurar respuestas:
 *    - GuideItemResponseObserver (created/updated/deleted/restored/forceDeleted)
 *    - GuideResponseObserver     (created/updated/deleted/restored/forceDeleted)
 *  **IMPORTANTE:** No recalcular en controladores/vistas; usar SIEMPRE los campos
 *  persistidos para listados (coherencia y performance).
 *
 *  Tipos de ítems calificables
 *  ---------------------------
 *  Definidos en TemplateItem::allowedScorable(): ['select','radio','scale'].
 *  - ‘text’ (y similares) NO puntúan.
 *  - Los valores provienen de la tabla scales (columna value). Para ‘radio’ y
 *    ‘select/scale’ guardamos ‘option’ numérico. (En versiones previas ‘checkbox’
 *    existía pero fue migrado a ‘radio’ por simplicidad.)
 *
 *  Soft deletes
 *  ------------
 *  Este modelo usa SoftDeletes. Los observers y el agregador emplean withTrashed()
 *  para no perder consistencia al restaurar/borrar entidades relacionadas.
 *
 *  Indexación recomendada (DB)
 *  ---------------------------
 *  - Índices simples: evaluator_id, participant_id, division_id, status, date, cycle.
 *  - Compuestos recomendados para listados: (evaluator_id, status, date), (participant_id, status).
 *
 *  Uso en UI/Reportes
 *  ------------------
 *  - Mostrar porcentajes con overall_avg_pct / answered_avg_pct (evitas multiplicar por 100).
 *  - En exportaciones usa los campos persistidos. No re-calcules con subqueries salvo necesidad.
 *
 *  Contratos de uso (do/don’t)
 *  ---------------------------
 *  ✔️ Usar scopes: ->signed()->whereParticipant(...) etc.
 *  ✔️ Usar needsSignatureFrom($userId, $rol) para habilitar botón “Firmar”.
 *  ✔️ Usar metrics persistidas (overall_avg, etc.) para listados/export.
 *  ❌ No llamar ScoreAggregator desde vistas/controladores de lectura.
 *  ❌ No forzar writes directos a total_score/max_score salvo desde el servicio.
 *
 * -----------------------------------------------------------------------------
 */

/**
 * Atributos y relaciones (IDE helpers)
 *
 * @property int $id
 * @property int $evaluator_id
 * @property int $participant_id
 * @property \Illuminate\Support\Carbon|null $date
 * @property string|null $cycle
 * @property string $status
 * @property string|null $comments
 * @property string|null $pdf_path
 * @property float $total_score
 * @property float|null $overall_avg // 0..1
 * @property float|null $answered_avg // 0..1
 * @property float $max_score
 * @property int|null $division_id
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Division|null $division
 * @property-read \App\Models\User $evaluator
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GuideResponse> $guideResponses
 * @property-read int|null $guide_responses_count
 * @property-read \App\Models\User $participant
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EvaluationSessionSignature> $signatures
 * @property-read int|null $signatures_count
 * @property-read string $status_name
 * @property-read float|null $overall_avg_pct
 * @property-read float|null $answered_avg_pct
 *
 * @method static Builder|EvaluationSession draft()
 * @method static Builder|EvaluationSession completed()
 * @method static Builder|EvaluationSession signed()
 * @method static Builder|EvaluationSession pending()
 * @method static Builder|EvaluationSession inProgress()
 * @method static Builder|EvaluationSession cancelled()
 * @method static Builder|EvaluationSession pendingSignatureFor(int $userId)
 *
 * @mixin \Eloquent
 */
class EvaluationSession extends Model
{
    use HasFactory, SoftDeletes;

    // --- Estados del dominio (string codes persistidos en DB) ---
    public const STATUS_DRAFT = 'draft';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_SIGNED = 'signed';      // coach ya firmó; falta coachee

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_PENDING = 'pending';

    /** Campos permitidos en asignación masiva.
     *  Nota: las métricas pueden ser ‘forceFill’ por los servicios/observers. */
    protected $fillable = [
        'evaluator_id',
        'participant_id',
        'division_id',
        'date',
        'cycle',
        'pdf_path',
        'comments',
        'status',
        // columnas de score (no estrictamente necesario por forceFill, pero útil):
        'total_score',
        'overall_avg',
        'answered_avg',
        'max_score',
    ];

    /** Valores por defecto (persistidos si el atributo no se establece). */
    protected $attributes = [
        'status' => self::STATUS_DRAFT,
        'total_score' => 0.0,
        'overall_avg' => null,
        'answered_avg' => null,
        'max_score' => 0.0,
    ];

    /** Casts nativos de Eloquent para lectura/escritura consistente. */
    protected $casts = [
        'date' => 'date',
        'status' => 'string',
        'total_score' => 'float',
        'overall_avg' => 'float',
        'answered_avg' => 'float',
        'max_score' => 'float',
    ];

    /** Atributos calculados incluidos al serializar a array/json (API/UI). */
    protected $appends = [
        'status_name',
        'overall_avg_pct',
        'answered_avg_pct',
    ];

    // ---------------------------------------------------------------------
    // Relaciones
    // ---------------------------------------------------------------------

    /** Coach dueño de la sesión. */
    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }

    /** Persona evaluada. */
    public function participant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'participant_id');
    }

    /** División organizacional (catálogo). */
    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class, 'division_id');
    }

    /** Respuestas por guía (cada guía instancia una plantilla). */
    public function guideResponses(): HasMany
    {
        return $this->hasMany(GuideResponse::class, 'session_id');
    }

    /** Firmas registradas (coach / coachee / aprobadores). */
    public function signatures(): HasMany
    {
        return $this->hasMany(EvaluationSessionSignature::class, 'session_id');
    }

    // ---------------------------------------------------------------------
    // Scopes por estado (para construir queries legibles y reutilizables)
    // ---------------------------------------------------------------------

    public function scopeDraft(Builder $q): Builder
    {
        return $q->where('status', self::STATUS_DRAFT);
    }

    public function scopeCompleted(Builder $q): Builder
    {
        return $q->where('status', self::STATUS_COMPLETED);
    }

    public function scopeSigned(Builder $q): Builder
    {
        return $q->where('status', self::STATUS_SIGNED);
    }

    public function scopePending(Builder $q): Builder
    {
        return $q->where('status', self::STATUS_PENDING);
    }

    public function scopeInProgress(Builder $q): Builder
    {
        return $q->where('status', self::STATUS_IN_PROGRESS);
    }

    public function scopeCancelled(Builder $q): Builder
    {
        return $q->where('status', self::STATUS_CANCELLED);
    }

    /**
     * Sesiones que requieren la firma del COACHEE indicado.
     * Regla: SOLO en estado SIGNED (el coach ya firmó) y sin firma ‘signed’ del coachee.
     *
     * Ejemplo de uso:
     *   EvaluationSession::pendingSignatureFor($userId)->get();
     */
    public function scopePendingSignatureFor(Builder $q, int $userId): Builder
    {
        return $q->where('status', self::STATUS_SIGNED)
            ->whereDoesntHave('signatures', function ($s) use ($userId) {
                $s->where('user_id', $userId)
                    ->where('signer_role', 'coachee')
                    ->where('status', 'signed');
            });
    }

    // ---------------------------------------------------------------------
    // Helpers de dominio (estado / firma)
    // ---------------------------------------------------------------------

    /** True si la sesión está completamente firmada por ambas partes. */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /** True si el coach ya firmó (estado SIGNED → falta coachee). */
    public function isSigned(): bool
    {
        return $this->status === self::STATUS_SIGNED;
    }

    /**
     * Determina si un $userId con $role debe/puede firmar.
     *
     * - COMPLETED / CANCELLED: nadie firma.
     * - COACHEE: sólo cuando la sesión está SIGNED.
     * - COACH  : mientras esté PENDING o IN_PROGRESS y él no haya firmado.
     *
     * Nota: Este método NO crea firmas; sólo gobierna la UI/permisos de acción.
     */
    public function needsSignatureFrom(int $userId, string $role = 'coachee'): bool
    {
        if (in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_CANCELLED], true)) {
            return false;
        }

        $alreadySigned = $this->relationLoaded('signatures')
            ? $this->signatures->contains(
                fn ($sig) => (int) $sig->user_id === (int) $userId
                    && $sig->signer_role === $role
                    && $sig->status === 'signed'
            )
            : $this->signatures()
                ->where('user_id', $userId)
                ->where('signer_role', $role)
                ->where('status', 'signed')
                ->exists();

        if ($alreadySigned) {
            return false;
        }

        return match ($role) {
            'coachee' => $this->isSigned(),
            'coach' => in_array($this->status, [self::STATUS_PENDING, self::STATUS_IN_PROGRESS], true),
            default => false,
        };
    }

    public function getSignatureData(string $role): array
    {
        $sig = $this->signatures->firstWhere('signer_role', $role);

        $user = $role === 'coach' ? $this->evaluator : $this->participant;
        $statusLabel = $this->labelForStatus($sig->status);

        return [
            'name' => $user->name ?? '—',
            'image_url' => $sig && $sig->digital_signature
                ? asset('storage/'.ltrim($sig->digital_signature, '/'))
                : null,
            'status' => $statusLabel,
            'signed_at' => $sig?->signed_at?->format('d/m/Y H:i') ?? '—',
        ];
    }

    // ---------------------------------------------------------------------
    // Etiquetas de estado (catálogo para UI/exports)
    // ---------------------------------------------------------------------

    /** Mapa de etiquetas en español para cada estado. */
    public static function statusLabels(): array
    {
        return [
            self::STATUS_DRAFT => 'Borrador',
            self::STATUS_IN_PROGRESS => 'En progreso',
            self::STATUS_PENDING => 'Pendiente',
            self::STATUS_COMPLETED => 'Completada',
            self::STATUS_SIGNED => 'Firmada',
            self::STATUS_CANCELLED => 'Cancelada',
        ];
    }

    /** Devuelve etiqueta de un estado. */
    public static function labelForStatus(string $status): string
    {
        return static::statusLabels()[$status] ?? ucfirst(str_replace('_', ' ', $status));
    }

    /** Accessor serializado: nombre legible del estado. */
    public function getStatusNameAttribute(): string
    {
        return static::labelForStatus($this->status);
    }

    /** Lista blanca de estados válidos (para validaciones). */
    public static function allowedStatuses(): array
    {
        return [
            self::STATUS_DRAFT,
            self::STATUS_COMPLETED,
            self::STATUS_SIGNED,
            self::STATUS_PENDING,
            self::STATUS_IN_PROGRESS,
            self::STATUS_CANCELLED,
        ];
    }

    // ---------------------------------------------------------------------
    // Catálogo Cycle (clave natural evaluation_sessions.cycle → cycles.key)
    // ---------------------------------------------------------------------

    public function cycleRow()
    {
        return $this->belongsTo(\App\Models\Cycle::class, 'cycle', 'key');
    }

    public function getCycleLabelAttribute(): string
    {
        return $this->cycleRow->label ?? ($this->cycle ?? '—');
    }

    public function getCycleFullNameAttribute(): string
    {
        return $this->cycleRow->full_name ?? ($this->cycle ?? '—');
    }

    // ---------------------------------------------------------------------
    // Presentación/UI: color de “pill” por estado
    // ---------------------------------------------------------------------

    protected function statusPillColor(): Attribute
    {
        return Attribute::make(
            get: fn () => match ($this->status) {
                self::STATUS_DRAFT => 'zinc',
                self::STATUS_IN_PROGRESS => 'green',
                self::STATUS_PENDING => 'yellow',
                self::STATUS_COMPLETED => 'blue',
                self::STATUS_SIGNED => 'orange',
                self::STATUS_CANCELLED => 'red',
                default => 'zinc',
            }
        );
    }

    // ---------------------------------------------------------------------
    // Utilidades de progreso (conteos informativos; no afectan cálculo de score)
    // ---------------------------------------------------------------------

    /** Plantillas (GuideTemplate) alcanzadas por la sesión vía GuideResponse. */
    public function templates()
    {
        return $this->hasManyThrough(
            \App\Models\GuideTemplate::class,
            \App\Models\GuideResponse::class,
            'session_id',
            'id',
            'id',
            'guide_template_id'
        );
    }

    /** GuideResponses marcadas como completas (si tu dominio define esa bandera). */
    public function completedGuideResponses()
    {
        return $this->guideResponses()->where('is_completed', true);
    }

    /** Alias historical (compatibilidad) */
    public static function statusLabel(string $status): string
    {
        return static::labelForStatus($status);
    }

    /** Total de ítems planeados en todas las guías de la sesión (informativo). */
    public function getPlannedItemsCountAttribute(): int
    {
        $templateIds = $this->guideResponses()->pluck('guide_template_id');
        if ($templateIds->isEmpty()) {
            return 0;
        }

        $sectionIds = \App\Models\TemplateSection::whereIn('guide_template_id', $templateIds)->pluck('id');
        if ($sectionIds->isEmpty()) {
            return 0;
        }

        return \App\Models\TemplateItem::whereIn('template_section_id', $sectionIds)->count();
    }

    /** Total de ítems respondidos (conteo de GuideItemResponse; informativo). */
    public function getAnsweredItemsCountAttribute(): int
    {
        $grIds = $this->guideResponses()->pluck('id');
        if ($grIds->isEmpty()) {
            return 0;
        }

        return \App\Models\GuideItemResponse::whereIn('guide_response_id', $grIds)->count();
    }

    /** Porcentaje de progreso = respondidos / planeados (sólo informativo). */
    public function getProgressPercentAttribute(): int
    {
        $total = $this->planned_items_count;

        return $total > 0 ? (int) round(($this->answered_items_count / $total) * 100) : 0;
    }

    // ---------------------------------------------------------------------
    // Accessors de porcentaje para UI/reportes (0..100)
    // ---------------------------------------------------------------------

    /** Promedio real (incluye no respondidas=0) en 0..100. */
    public function getOverallAvgPctAttribute(): ?float
    {
        return $this->overall_avg !== null
            ? round((float) $this->overall_avg * 100, 2)
            : null;
    }

    /** Promedio actual (sólo respondidas) en 0..100. */
    public function getAnsweredAvgPctAttribute(): ?float
    {
        return $this->answered_avg !== null
            ? round((float) $this->answered_avg * 100, 2)
            : null;
    }
}
