<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Operations\Services;

use App\Domains\Intelligence\Operations\Models\EnterpriseMission;
use App\Domains\Intelligence\Operations\Models\MissionLearningCycle;

class LearningOptimizationService
{
    public function capture(EnterpriseMission $mission): MissionLearningCycle
    {
        return MissionLearningCycle::query()->create([
            'enterprise_mission_id' => $mission->id,
            'learning_type' => 'autonomous_optimization',
            'status' => 'captured',
            'improvement_summary' => 'Prompt quality, planning quality, routing, tool selection, approvals, verification, and recovery signals captured.',
            'improvement_payload' => [
                'prompt_quality' => 'improve-example-density',
                'planning_quality' => 'reduce-critical-path-ambiguity',
                'routing' => 'prefer-high-trust-providers',
                'tool_selection' => 'penalize-high-latency-tools',
                'agent_selection' => 'rebalance-on-workload',
                'approval_routing' => 'escalate-delayed-approvals',
                'verification' => 'increase-evidence-threshold',
                'failure_recovery' => 'checkpoint-more-frequently',
            ],
        ]);
    }
}
