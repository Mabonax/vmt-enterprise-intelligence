<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Agents\Models;

class AgentFeedback extends AgentRecord
{
    protected $fillable = ['agent_session_id', 'user_id', 'rating', 'comment', 'metadata'];

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }
}
