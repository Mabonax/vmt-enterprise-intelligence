<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Agents\Models;

class AgentExecutionLog extends AgentRecord
{
    protected $fillable = ['agent_session_id', 'agent_workflow_step_id', 'agent_id', 'event', 'status', 'message', 'payload', 'metadata'];

    protected function casts(): array
    {
        return ['payload' => 'array', 'metadata' => 'array'];
    }
}
