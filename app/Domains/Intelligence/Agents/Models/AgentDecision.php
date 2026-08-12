<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Agents\Models;

class AgentDecision extends AgentRecord
{
    protected $fillable = ['agent_session_id', 'agent_id', 'decision_key', 'why', 'alternatives', 'chosen_path', 'confidence', 'risks', 'references', 'tool_evidence', 'knowledge_evidence', 'metadata'];

    protected function casts(): array
    {
        return ['alternatives' => 'array', 'confidence' => 'float', 'risks' => 'array', 'references' => 'array', 'tool_evidence' => 'array', 'knowledge_evidence' => 'array', 'metadata' => 'array'];
    }
}
