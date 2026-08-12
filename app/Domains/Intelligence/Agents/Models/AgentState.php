<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Agents\Models;

class AgentState extends AgentRecord
{
    protected $table = 'agent_state';

    protected $fillable = ['agent_id', 'agent_session_id', 'current_status', 'current_task_id', 'working_memory', 'metadata'];

    protected function casts(): array
    {
        return ['working_memory' => 'array', 'metadata' => 'array'];
    }
}
