<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Carbon\Carbon;

class UserData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public ?\Carbon\Carbon $email_verified_at,
        public string $password,
        public ?string $remember_token,
        public mixed $_lft,
        public mixed $_rgt,
        public mixed $parent_id
    ) {}
    
    public static function fromModel($model): self
    {
        return new self(
            id: $model->id,
            name: $model->name,
            email: $model->email,
            email_verified_at: $model->email_verified_at,
            password: $model->password,
            remember_token: $model->remember_token,
            _lft: $model->_lft,
            _rgt: $model->_rgt,
            parent_id: $model->parent_id
        );
    }
}
