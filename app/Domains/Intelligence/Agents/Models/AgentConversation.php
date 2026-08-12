<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Agents\Models;

class AgentConversation extends AgentRecord
{
    protected $fillable = ['agent_session_id', 'topic', 'status', 'participants', 'metadata'];

    protected function casts(): array
    {
        return ['participants' => 'array', 'metadata' => 'array'];
    }
}
