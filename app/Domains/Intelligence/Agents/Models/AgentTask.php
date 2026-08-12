<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Agents\Models;

class AgentTask extends AgentRecord
{
    protected $fillable = ['agent_session_id', 'parent_task_id', 'owner_agent_id', 'title', 'objective', 'status', 'priority', 'task_type', 'sequence', 'dependencies', 'input_payload', 'output_payload', 'metadata'];

    protected function casts(): array
    {
        return ['dependencies' => 'array', 'input_payload' => 'array', 'output_payload' => 'array', 'metadata' => 'array'];
    }
}
