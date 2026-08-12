<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Agents\Models;

class AgentNotification extends AgentRecord
{
    protected $fillable = ['agent_session_id', 'user_id', 'type', 'status', 'message', 'payload', 'metadata'];

    protected function casts(): array
    {
        return ['payload' => 'array', 'metadata' => 'array'];
    }
}
