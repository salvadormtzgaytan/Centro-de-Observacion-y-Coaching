<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $session_id
 * @property int $user_id
 * @property string $signer_role
 * @property \Illuminate\Support\Carbon|null $signed_at
 * @property string|null $digital_signature
 * @property string|null $method
 * @property string $status
 * @property string|null $rejection_reason
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\EvaluationSession $session
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationSessionSignature newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationSessionSignature newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationSessionSignature query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationSessionSignature whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationSessionSignature whereDigitalSignature($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationSessionSignature whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationSessionSignature whereMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationSessionSignature whereRejectionReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationSessionSignature whereSessionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationSessionSignature whereSignedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationSessionSignature whereSignerRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationSessionSignature whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationSessionSignature whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationSessionSignature whereUserId($value)
 * @mixin \Eloquent
 */
class EvaluationSessionSignature extends Model
{
    use HasFactory;

    protected $table = 'evaluation_session_signatures';

    protected $fillable = [
        'session_id',
        'user_id',
        'signer_role',
        'signed_at',
        'digital_signature',
        'method',
        'status',
        'rejection_reason',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
    ];

    /**
     * La sesión de evaluación a la que pertenece esta firma.
     */
    public function session()
    {
        return $this->belongsTo(EvaluationSession::class, 'session_id');
    }

    /**
     * Usuario firmante.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
