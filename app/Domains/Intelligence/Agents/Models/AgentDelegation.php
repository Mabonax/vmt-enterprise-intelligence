<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Agents\Models;

class AgentDelegation extends AgentRecord
{
    protected $fillable = ['agent_session_id', 'source_agent_id', 'target_agent_id', 'agent_task_id', 'status', 'objective', 'handover_payload', 'result_payload', 'metadata'];

    protected function casts(): array
    {
        return ['handover_payload' => 'array', 'result_payload' => 'array', 'metadata' => 'array'];
    }
}
