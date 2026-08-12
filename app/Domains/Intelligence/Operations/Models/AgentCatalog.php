<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Operations\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class AgentCatalog extends OperationsRecord
{
    protected $table = 'operations_agent_catalog';

    protected $fillable = [
        'agent_id',
        'name',
        'slug',
        'provider',
        'preferred_model',
        'status',
        'historical_success_rate',
        'trust_score',
        'average_latency_ms',
        'average_cost',
        'current_workload',
        'permissions',
        'tool_ownership',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'historical_success_rate' => 'decimal:2',
            'trust_score' => 'decimal:2',
            'average_cost' => 'decimal:2',
            'current_workload' => 'decimal:2',
            'permissions' => 'array',
            'tool_ownership' => 'array',
            'metadata' => 'array',
        ];
    }

    public function capabilities(): HasMany
    {
        return $this->hasMany(AgentCapability::class, 'agent_catalog_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(AgentVersion::class, 'agent_catalog_id');
    }

    public function skills(): HasMany
    {
        return $this->hasMany(AgentSkill::class, 'agent_catalog_id');
    }

    public function availability(): HasMany
    {
        return $this->hasMany(AgentAvailability::class, 'agent_catalog_id');
    }
}
