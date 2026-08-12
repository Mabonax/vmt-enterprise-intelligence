<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Agents\Models;

class AgentRole extends AgentRecord
{
    protected $fillable = ['role_key', 'name', 'description', 'default_prompt', 'capabilities', 'preferred_tools', 'reasoning_style', 'risk_tolerance', 'verification_strategy', 'memory_scope', 'metadata'];

    protected function casts(): array
    {
        return ['capabilities' => 'array', 'preferred_tools' => 'array', 'metadata' => 'array'];
    }
}
