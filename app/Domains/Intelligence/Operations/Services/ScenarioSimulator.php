<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Operations\Services;

use App\Domains\Intelligence\Operations\Models\EnterpriseMission;
use App\Domains\Intelligence\Operations\Models\ScenarioSimulation;

class ScenarioSimulator
{
    public function simulate(EnterpriseMission $mission, string $scenarioType, array $context = []): ScenarioSimulation
    {
        $delay = match ($scenarioType) {
            'provider_outage' => 12,
            'agent_failure' => 8,
            'tool_failure' => 6,
            'budget_reduction' => 16,
            'knowledge_loss' => 10,
            'compliance_failure' => 20,
            default => 4,
        };

        return ScenarioSimulation::query()->create([
            'enterprise_mission_id' => $mission->id,
            'scenario_type' => $scenarioType,
            'scenario_label' => str_replace('_', ' ', $scenarioType),
            'status' => 'simulated',
            'predicted_outcome' => [
                'delay_hours' => $delay,
                'completion_probability' => max(30, 92 - ($delay * 2)),
            ],
            'impact_summary' => [
                'budget_delta' => round($delay * 125, 2),
                'risk_delta' => min(100, 20 + $delay),
            ],
            'simulation_payload' => $context,
            'metadata' => [
                'preventative_recommendation' => 'Pre-stage an alternate provider and rebalance the execution team.',
            ],
        ]);
    }
}
