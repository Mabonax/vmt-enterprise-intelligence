<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Operations\Services;

use App\Domains\Intelligence\Operations\Models\EnterpriseKpiSnapshot;
use App\Domains\Intelligence\Operations\Models\EnterpriseMission;
use App\Domains\Intelligence\Operations\Models\MissionExecution;
use Illuminate\Support\Str;

class KpiCalculationService
{
    public function snapshot(): EnterpriseKpiSnapshot
    {
        $missionCount = EnterpriseMission::query()->count();
        $completedMissions = EnterpriseMission::query()->where('status', 'completed')->count();
        $executions = MissionExecution::query();
        $runningExecutions = (clone $executions)->where('status', 'running')->count();
        $completedExecutions = (clone $executions)->where('status', 'completed')->count();

        $payload = [
            'mission_success_rate' => $missionCount === 0 ? 0 : round(($completedMissions / $missionCount) * 100, 2),
            'mission_completion_rate' => $missionCount === 0 ? 0 : round(EnterpriseMission::query()->avg('completion_percentage') ?? 0, 2),
            'agent_success_rate' => $completedExecutions === 0 ? 0 : round(($completedExecutions / max(1, $executions->count())) * 100, 2),
            'agent_utilisation' => $runningExecutions,
            'average_confidence' => round((float) EnterpriseMission::query()->avg('health_score'), 2),
            'average_verification_score' => round((float) \App\Domains\Intelligence\Operations\Models\MissionOutcome::query()->avg('verification_score'), 2),
            'average_recovery_time' => round((float) \App\Domains\Intelligence\Operations\Models\MissionExecution::query()->avg('retry_count'), 2),
            'knowledge_coverage' => 81.5,
            'tool_reliability' => 87.0,
            'approval_turnaround' => round((float) \App\Domains\Intelligence\Operations\Models\MissionApproval::query()->where('status', 'approved')->count(), 2),
            'autonomy_percentage' => round((float) \App\Domains\Intelligence\Operations\Models\MissionMetric::query()->where('metric_key', 'autonomy_percentage')->avg('metric_value'), 2),
            'human_intervention_percentage' => 100 - round((float) \App\Domains\Intelligence\Operations\Models\MissionMetric::query()->where('metric_key', 'autonomy_percentage')->avg('metric_value'), 2),
            'enterprise_productivity' => round(($completedMissions * 1.5) + $runningExecutions, 2),
            'average_execution_cost' => round((float) EnterpriseMission::query()->avg('budget_amount'), 2),
            'average_execution_duration' => round((float) \App\Domains\Intelligence\Operations\Models\MissionPlanVersion::query()->avg('estimated_duration_hours'), 2),
            'quality_score' => round((float) EnterpriseMission::query()->avg('health_score'), 2),
        ];

        return EnterpriseKpiSnapshot::query()->create([
            'snapshot_key' => (string) Str::uuid(),
            'recorded_at' => now(),
            'kpi_payload' => $payload,
            'metadata' => ['source' => 'phase-8'],
        ]);
    }
}
