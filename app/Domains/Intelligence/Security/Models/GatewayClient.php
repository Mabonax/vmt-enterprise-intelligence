<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Security\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GatewayClient extends Model
{
    use HasUuids;

    public $incrementing = false;

    protected $guarded = [];

    protected $keyType = 'string';

    protected function casts(): array
    {
        return [
            'allowed_origins' => 'array',
            'allowed_ips' => 'array',
            'enabled_providers' => 'array',
            'enabled_models' => 'array',
            'enabled_capabilities' => 'array',
            'metadata' => 'array',
            'last_used_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(GatewayTenant::class, 'gateway_tenant_id');
    }

    public function credentials(): HasMany
    {
        return $this->hasMany(GatewayApiCredential::class);
    }
}
