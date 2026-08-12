<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Agents\Models;

class AgentApproval extends AgentRecord
{
    protected $fillable = ['agent_session_id', 'agent_workflow_id', 'approval_type', 'requested_role', 'requested_by_user_id', 'approved_by_user_id', 'status', 'reason', 'decision_notes', 'decided_at', 'metadata'];

    protected function casts(): array
    {
        return ['decided_at' => 'datetime', 'metadata' => 'array'];
    }
}
