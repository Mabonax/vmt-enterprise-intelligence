<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Agents\Models;

class AgentCheckpoint extends AgentRecord
{
    protected $fillable = ['agent_session_id', 'checkpoint_type', 'status', 'snapshot_payload', 'metadata'];

    protected function casts(): array
    {
        return ['snapshot_payload' => 'array', 'metadata' => 'array'];
    }
}
