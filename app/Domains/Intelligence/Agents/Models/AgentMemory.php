<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Agents\Models;

class AgentMemory extends SoftDeletingAgentRecord
{
    protected $table = 'agent_memory';

    protected $fillable = ['agent_id', 'agent_session_id', 'memory_key', 'memory_type', 'scope', 'title', 'content', 'references', 'metadata', 'expires_at'];

    protected function casts(): array
    {
        return ['references' => 'array', 'metadata' => 'array', 'expires_at' => 'datetime'];
    }
}
