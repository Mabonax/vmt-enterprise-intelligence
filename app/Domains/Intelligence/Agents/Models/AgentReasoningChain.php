<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Agents\Models;

class AgentReasoningChain extends AgentRecord
{
    protected $fillable = ['agent_session_id', 'agent_id', 'goal', 'plan', 'actions', 'tool_usage', 'knowledge_references', 'verification', 'confidence', 'decision', 'result_summary', 'metadata'];

    protected function casts(): array
    {
        return ['plan' => 'array', 'actions' => 'array', 'tool_usage' => 'array', 'knowledge_references' => 'array', 'verification' => 'array', 'confidence' => 'float', 'metadata' => 'array'];
    }
}
