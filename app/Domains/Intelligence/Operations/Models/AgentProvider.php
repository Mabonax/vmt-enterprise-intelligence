<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Operations\Models;

class AgentProvider extends OperationsRecord
{
    protected $table = 'operations_agent_providers';

    protected $fillable = [
        'provider_key',
        'name',
        'status',
        'latency_ms',
        'health_score',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'health_score' => 'decimal:2',
            'metadata' => 'array',
        ];
    }
}
