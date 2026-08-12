<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Agents\Models;

class AgentSession extends SoftDeletingAgentRecord
{
    protected $fillable = ['agent_id', 'user_id', 'session_key', 'title', 'objective', 'status', 'execution_mode', 'approval_role', 'requires_human_approval', 'started_at', 'completed_at', 'context_payload', 'result_payload', 'metadata'];

    protected function casts(): array
    {
        return ['requires_human_approval' => 'bool', 'started_at' => 'datetime', 'completed_at' => 'datetime', 'context_payload' => 'array', 'result_payload' => 'array', 'metadata' => 'array'];
    }
}
