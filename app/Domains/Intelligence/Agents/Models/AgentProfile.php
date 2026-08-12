<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Agents\Models;

class AgentProfile extends AgentRecord
{
    protected $fillable = ['agent_id', 'agent_role_id', 'identity', 'prompt', 'preferred_tools', 'capabilities', 'reasoning_style', 'risk_tolerance', 'verification_strategy', 'memory_scope', 'metadata'];

    protected function casts(): array
    {
        return ['preferred_tools' => 'array', 'capabilities' => 'array', 'metadata' => 'array'];
    }
}
