<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Agents\Models;

class AgentVerification extends AgentRecord
{
    protected $fillable = ['agent_session_id', 'status', 'confidence', 'missing_evidence', 'tool_coverage', 'knowledge_coverage', 'metadata'];

    protected function casts(): array
    {
        return ['confidence' => 'float', 'missing_evidence' => 'array', 'tool_coverage' => 'array', 'knowledge_coverage' => 'array', 'metadata' => 'array'];
    }
}
