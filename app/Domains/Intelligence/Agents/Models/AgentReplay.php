<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Agents\Models;

class AgentReplay extends AgentRecord
{
    protected $table = 'agent_replay';

    protected $fillable = ['agent_session_id', 'timeline', 'reasoning_graph', 'metadata'];

    protected function casts(): array
    {
        return ['timeline' => 'array', 'reasoning_graph' => 'array', 'metadata' => 'array'];
    }
}
