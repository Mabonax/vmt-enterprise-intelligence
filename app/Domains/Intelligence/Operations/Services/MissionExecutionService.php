<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Operations\Services;

use App\Domains\Intelligence\Operations\Models\EnterpriseMission;
use App\Domains\Intelligence\Operations\Models\MissionExecution;
use App\Domains\Intelligence\Operations\Models\MissionMetric;
use App\Domains\Intelligence\Operations\Models\MissionOutcome;
use Illuminate\Support\Str;

class MissionExecutionService
{
    public function __construct(
        private readonly AgentTeamBuilder $teamBuilder,
        private readonly PolicyEngine $policyEngine,
        private readonly ComplianceVerifier $complianceVerifier,
        private readonly MissionHealthService $healthService,
        private readonly EnterpriseEventBus $eventBus,
    ) {}

    public function start(EnterpriseMission $mission, string $mode = 'autonomous'): MissionExecution
    {
        $latestPlan = $mission->planVersions()->latest('version_number')->first();
        $team = $this->teamBuilder->buildForMission($mission, $latestPlan?->required_agent_skills ?? []);
        $policy = $this->policyEngine->evaluateMission($mission, $latestPlan);
        $compliance = $this->complianceVerifier->evaluateMission($mission);

        $status = ($policy->status === 'approved' && $compliance->status !== 'blocked') ? 'running' : 'awaiting_approval';

        $execution = MissionExecution::query()->create([
            'enterprise_mission_id' => $mission->id,
            'execution_key' => (string) Str::uuid(),
            'status' => $status,
            'execution_mode' => $mode,
            'assigned_team' => count($team) > 1 ? 'dynamic' : 'solo',
            'assigned_agents' => $team,
            'snapshot_payload' => ['plan_version_id' => $latestPlan?->id],
            'started_at' => now(),
            'last_checkpoint_at' => now(),
            'metadata' => [
                'policy_status' => $policy->status,
                'compliance_status' => $compliance->status,
            ],
        ]);

        $mission->forceFill([
            'status' => $status === 'running' ? 'active' : 'awaiting_approval',
            'started_at' => $mission->started_at ?? now(),
            'estimated_completion_at' => $mission->started_at?->copy()->addHours((int) round((float) ($latestPlan?->estimated_duration_hours ?? 24))) ?? now()->addHours((int) round((float) ($latestPlan?->estimated_duration_hours ?? 24))),
            'health_score' => $this->healthService->score($mission),
        ])->save();

        MissionMetric::query()->create([
            'enterprise_mission_id' => $mission->id,
            'metric_key' => 'autonomy_percentage',
            'metric_value' => $status === 'running' ? 82.50 : 48.00,
            'unit' => 'percent',
            'recorded_at' => now(),
        ]);

        MissionOutcome::query()->create([
            'enterprise_mission_id' => $mission->id,
            'outcome_type' => 'execution_state',
            'status' => $status,
            'summary' => $status === 'running' ? 'Mission execution launched successfully.' : 'Mission execution paused pending approval.',
            'verification_score' => $status === 'running' ? 88 : 72,
            'confidence_score' => $status === 'running' ? 84 : 68,
            'payload' => ['team_size' => count($team)],
        ]);

        $this->eventBus->publish('mission.started', $mission, ['execution_id' => $execution->id, 'status' => $status], MissionExecution::class, $execution->id);

        return $execution;
    }

    public function complete(MissionExecution $execution, array $result = []): MissionExecution
    {
        $execution->forceFill([
            'status' => 'completed',
            'result_payload' => $result,
            'completed_at' => now(),
        ])->save();

        $mission = $execution->mission;
        $mission->forceFill([
            'status' => 'completed',
            'completed_at' => now(),
            'completion_percentage' => 100,
            'health_score' => $this->healthService->score($mission),
        ])->save();

        $this->eventBus->publish('mission.completed', $mission, ['execution_id' => $execution->id], MissionExecution::class, $execution->id);

        return $execution->fresh();
    }
}
