<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Agents\Models;

class AgentCapability extends AgentRecord
{
    protected $fillable = ['agent_id', 'agent_role_id', 'capability_key', 'capability_type', 'confidence_weight', 'metadata'];

    protected function casts(): array
    {
        return ['confidence_weight' => 'float', 'metadata' => 'array'];
    }
}
