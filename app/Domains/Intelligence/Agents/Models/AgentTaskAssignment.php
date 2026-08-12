<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Agents\Models;

class AgentTaskAssignment extends AgentRecord
{
    protected $fillable = ['agent_task_id', 'agent_id', 'agent_role_id', 'assignment_type', 'status', 'metadata'];

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }
}
