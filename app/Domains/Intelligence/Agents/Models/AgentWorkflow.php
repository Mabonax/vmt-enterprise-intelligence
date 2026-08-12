<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Agents\Models;

class AgentWorkflow extends AgentRecord
{
    protected $fillable = ['agent_session_id', 'lead_agent_id', 'name', 'status', 'execution_mode', 'branching_rules', 'approval_gates', 'rollback_payload', 'metadata'];

    protected function casts(): array
    {
        return ['branching_rules' => 'array', 'approval_gates' => 'array', 'rollback_payload' => 'array', 'metadata' => 'array'];
    }
}
