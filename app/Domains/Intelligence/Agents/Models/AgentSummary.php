<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Agents\Models;

class AgentSummary extends AgentRecord
{
    protected $fillable = ['agent_session_id', 'summary_type', 'summary', 'highlights', 'metadata'];

    protected function casts(): array
    {
        return ['highlights' => 'array', 'metadata' => 'array'];
    }
}
