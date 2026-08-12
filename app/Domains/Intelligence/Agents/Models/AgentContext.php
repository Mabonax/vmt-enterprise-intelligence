<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Agents\Models;

class AgentContext extends AgentRecord
{
    protected $fillable = ['agent_id', 'agent_session_id', 'context_key', 'context_payload', 'metadata'];

    protected function casts(): array
    {
        return ['context_payload' => 'array', 'metadata' => 'array'];
    }
}
