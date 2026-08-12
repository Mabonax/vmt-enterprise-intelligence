<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Agents\Models;

class AgentCollaboration extends AgentRecord
{
    protected $fillable = ['agent_session_id', 'source_agent_id', 'target_agent_id', 'collaboration_type', 'payload', 'metadata'];

    protected function casts(): array
    {
        return ['payload' => 'array', 'metadata' => 'array'];
    }
}
