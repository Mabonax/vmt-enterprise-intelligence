<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Operations\Services;

use App\Domains\Intelligence\Operations\Models\EnterpriseMission;
use App\Domains\Intelligence\Operations\Models\MissionPlanVersion;
use App\Domains\Intelligence\Operations\Models\MissionPolicy;

class PolicyEngine
{
    public function evaluateMission(EnterpriseMission $mission, ?MissionPlanVersion $plan = null): MissionPolicy
    {
        $plan ??= $mission->planVersions()->latest('version_number')->first();
        $violations = [];

        if (($mission->budget_amount ?? 0) > 250000) {
            $violations[] = 'Budget exceeds autonomous execution threshold.';
        }

        if (($plan?->estimated_token_cost ?? 0) > 50000) {
            $violations[] = 'Estimated token usage exceeds default policy envelope.';
        }

        $status = $violations === [] ? 'approved' : 'review_required';
        $score = $violations === [] ? 92.0 : max(45.0, 85.0 - (count($violations) * 15));

        return MissionPolicy::query()->create([
            'enterprise_mission_id' => $mission->id,
            'policy_type' => 'execution',
            'status' => $status,
            'evaluation_score' => $score,
            'violations' => $violations,
            'recommendations' => $violations === [] ? ['Execution is within configured governance guardrails.'] : ['Route the mission through approval workflow before execution.'],
            'metadata' => [
                'allowed_providers' => ['openai', 'anthropic', 'ollama', 'gemini', 'lmstudio'],
                'approval_required' => $violations !== [],
            ],
        ]);
    }
}
