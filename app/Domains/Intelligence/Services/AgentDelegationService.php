<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Services;

use App\Domains\Intelligence\Models\Agent;
use App\Domains\Intelligence\Models\AgentDelegation;
use App\Domains\Intelligence\Models\ExecutionTrace;

class AgentDelegationService
{
    public function delegate(ExecutionTrace $trace, Agent $sourceAgent, Agent $targetAgent, string $objective, array $context = []): AgentDelegation
    {
        return AgentDelegation::query()->create([
            'execution_trace_id' => $trace->id,
            'source_agent_id' => $sourceAgent->id,
            'target_agent_id' => $targetAgent->id,
            'status' => 'completed',
            'objective' => $objective,
            'shared_context' => $context,
            'result_payload' => ['delegated' => true],
        ]);
    }
}
