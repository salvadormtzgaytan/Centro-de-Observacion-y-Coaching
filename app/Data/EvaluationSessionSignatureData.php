<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Carbon\Carbon;

class EvaluationSessionSignatureData extends Data
{
    public function __construct(
        public int $id,
        public int $session_id,
        public int $user_id,
        public string $signer_role,
        public ?\Carbon\Carbon $signed_at,
        public ?string $digital_signature,
        public ?string $method,
        public string $status,
        public ?string $rejection_reason
    ) {}
    
    public static function fromModel($model): self
    {
        return new self(
            id: $model->id,
            session_id: $model->session_id,
            user_id: $model->user_id,
            signer_role: $model->signer_role,
            signed_at: $model->signed_at,
            digital_signature: $model->digital_signature,
            method: $model->method,
            status: $model->status,
            rejection_reason: $model->rejection_reason
        );
    }
}
