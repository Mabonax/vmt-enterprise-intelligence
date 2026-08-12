<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Agents\Models;

class AgentQueue extends AgentRecord
{
    protected $table = 'agent_queue';

    protected $fillable = ['agent_session_id', 'queue_name', 'status', 'attempts', 'available_at', 'started_at', 'completed_at', 'metadata'];

    protected function casts(): array
    {
        return ['available_at' => 'datetime', 'started_at' => 'datetime', 'completed_at' => 'datetime', 'metadata' => 'array'];
    }
}
