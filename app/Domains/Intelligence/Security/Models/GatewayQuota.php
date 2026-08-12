<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Security\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class GatewayQuota extends Model
{
    use HasUuids;

    public $incrementing = false;

    protected $guarded = [];

    protected $keyType = 'string';

    protected function casts(): array
    {
        return [
            'window_starts_at' => 'datetime',
            'window_ends_at' => 'datetime',
            'metadata' => 'array',
        ];
    }
}
