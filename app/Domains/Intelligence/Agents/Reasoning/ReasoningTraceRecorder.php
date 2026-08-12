<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Agents\Reasoning;

use App\Domains\Intelligence\Agents\Models\AgentReasoningChain;
use App\Domains\Intelligence\Agents\Models\AgentThoughtSnapshot;

class ReasoningTraceRecorder
{
    public function start(string $sessionId, ?string $agentId, string $goal, array $plan, array $knowledgeReferences = []): AgentReasoningChain
    {
        return AgentReasoningChain::query()->create([
            'agent_session_id' => $sessionId,
            'agent_id' => $agentId,
            'goal' => $goal,
            'plan' => $plan,
            'knowledge_references' => $knowledgeReferences,
            'actions' => [],
            'tool_usage' => [],
            'verification' => [],
            'confidence' => 0.75,
        ]);
    }

    public function snapshot(AgentReasoningChain $chain, string $type, string $summary, array $evidence = [], array $metadata = []): AgentThoughtSnapshot
    {
        return AgentThoughtSnapshot::query()->create([
            'agent_reasoning_chain_id' => $chain->id,
            'snapshot_type' => $type,
            'summary' => $summary,
            'evidence' => $evidence,
            'metadata' => $metadata,
        ]);
    }
}
