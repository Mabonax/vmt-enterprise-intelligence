<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Agents\Models;

class AgentWorkflowStep extends AgentRecord
{
    protected $fillable = ['agent_workflow_id', 'agent_task_id', 'name', 'sequence', 'status', 'step_kind', 'is_parallel', 'requires_approval', 'condition_payload', 'result_payload', 'metadata'];

    protected function casts(): array
    {
        return ['is_parallel' => 'bool', 'requires_approval' => 'bool', 'condition_payload' => 'array', 'result_payload' => 'array', 'metadata' => 'array'];
    }
}
