<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Agents\Models;

class AgentThoughtSnapshot extends AgentRecord
{
    protected $fillable = ['agent_reasoning_chain_id', 'snapshot_type', 'summary', 'evidence', 'metadata'];

    protected function casts(): array
    {
        return ['evidence' => 'array', 'metadata' => 'array'];
    }
}
