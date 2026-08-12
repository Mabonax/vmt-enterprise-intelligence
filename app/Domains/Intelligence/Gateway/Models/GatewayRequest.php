<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Gateway\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GatewayRequest extends Model
{
    use HasFactory;
    use HasUuids;

    public $incrementing = false;

    protected $table = 'gateway_requests';

    protected $keyType = 'string';

    protected $fillable = [
        'organization_id',
        'gateway_tenant_id',
        'gateway_client_id',
        'connected_erp_id',
        'auth_method',
        'provider_profile_id',
        'capability',
        'status',
        'correlation_id',
        'provider',
        'model',
        'verification_passed',
        'input_tokens',
        'output_tokens',
        'latency_ms',
        'cost',
        'request_payload',
        'response_payload',
        'verification_payload',
        'tool_payload',
        'scopes',
        'failure_reason',
        'ip_address',
        'metadata',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'verification_passed' => 'boolean',
            'request_payload' => 'array',
            'response_payload' => 'array',
            'verification_payload' => 'array',
            'tool_payload' => 'array',
            'scopes' => 'array',
            'metadata' => 'array',
            'completed_at' => 'datetime',
            'cost' => 'float',
        ];
    }
}
