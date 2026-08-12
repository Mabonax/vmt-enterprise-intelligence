<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Operations\Services;

use App\Domains\Intelligence\Operations\Models\EnterpriseMission;
use App\Domains\Intelligence\Operations\Models\MissionMilestone;
use App\Domains\Intelligence\Operations\Models\MissionRisk;
use App\Models\User;
use Illuminate\Support\Str;

class ExecutiveOrchestrator
{
    public function __construct(
        private readonly MissionPlanner $planner,
        private readonly MissionExecutionService $executionService,
        private readonly ApprovalWorkflowService $approvalWorkflow,
        private readonly DecisionAnalysisService $decisionAnalysis,
        private readonly LearningOptimizationService $learningOptimization,
        private readonly EnterpriseEventBus $eventBus,
        private readonly MissionHealthService $healthService,
    ) {}

    public function launch(User $owner, array $attributes): EnterpriseMission
    {
        $mission = EnterpriseMission::query()->create([
            'owner_user_id' => $owner->id,
            'mission_key' => strtoupper(Str::slug((string) ($attributes['title'] ?? 'mission'))).'-'.Str::upper(Str::random(6)),
            'title' => (string) $attributes['title'],
            'objective_summary' => (string) $attributes['objective_summary'],
            'priority' => (string) ($attributes['priority'] ?? 'medium'),
            'status' => 'draft',
            'owner_name' => (string) ($attributes['owner_name'] ?? $owner->name),
            'budget_amount' => $attributes['budget_amount'] ?? null,
            'budget_currency' => (string) ($attributes['budget_currency'] ?? 'USD'),
            'deadline_at' => $attributes['deadline_at'] ?? null,
            'mission_payload' => $attributes['mission_payload'] ?? [],
            'metadata' => $attributes['metadata'] ?? [],
        ]);

        $this->eventBus->publish('mission.created', $mission, ['title' => $mission->title], EnterpriseMission::class, $mission->id);

        $plan = $this->planner->plan($mission);

        foreach (['Initiative approved', 'Programmes defined', 'Projects decomposed', 'Execution ready'] as $label) {
            MissionMilestone::query()->create([
                'enterprise_mission_id' => $mission->id,
                'title' => $label,
                'status' => 'pending',
                'target_at' => $mission->deadline_at,
            ]);
        }

        MissionRisk::query()->create([
            'enterprise_mission_id' => $mission->id,
            'title' => 'Execution complexity',
            'risk_type' => 'delivery',
            'severity' => $plan->complexity === 'high' ? 'high' : 'medium',
            'probability' => 'possible',
            'impact_score' => $plan->risk_score,
            'status' => 'open',
            'mitigation_plan' => 'Use dynamic team rebalancing, checkpoint recovery, and approval gating.',
        ]);

        $mission->forceFill([
            'risk_score' => $plan->risk_score,
            'health_score' => $this->healthService->score($mission),
            'estimated_completion_at' => now()->addHours((int) round((float) $plan->estimated_duration_hours)),
        ])->save();

        if (($attributes['require_approval'] ?? false) === true) {
            $this->approvalWorkflow->request($mission, [
                'approval_type' => 'sequential',
                'requested_role' => 'executive',
                'requested_by_user_id' => $owner->id,
            ]);
        }

        $this->decisionAnalysis->record($mission, [
            'decision_key' => 'initial-orchestration-path',
            'reasoning' => 'Mission was decomposed into additive phases and prepared for autonomous execution.',
            'chosen_option' => 'autonomous-operations-lifecycle',
            'confidence_score' => $plan->confidence_score,
        ]);

        $this->learningOptimization->capture($mission);

        return $mission->fresh();
    }

    public function execute(EnterpriseMission $mission, string $mode = 'autonomous'): EnterpriseMission
    {
        $execution = $this->executionService->start($mission, $mode);

        $mission->forceFill([
            'status' => $execution->status === 'running' ? 'active' : 'awaiting_approval',
        ])->save();

        return $mission->fresh(['executions', 'planVersions', 'approvals']);
    }
}
