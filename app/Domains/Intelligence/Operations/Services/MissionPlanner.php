<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Operations\Services;

use App\Domains\Intelligence\Operations\Models\EnterpriseMission;
use App\Domains\Intelligence\Operations\Models\MissionDependency;
use App\Domains\Intelligence\Operations\Models\MissionObjective;
use App\Domains\Intelligence\Operations\Models\MissionPhase;
use App\Domains\Intelligence\Operations\Models\MissionPlanVersion;
use App\Domains\Intelligence\Services\PlanningEngine;

class MissionPlanner
{
    public function __construct(
        private readonly PlanningEngine $planningEngine,
    ) {}

    public function plan(EnterpriseMission $mission): MissionPlanVersion
    {
        $version = (int) $mission->planVersions()->max('version_number') + 1;
        $basePlan = $this->planningEngine->plan($mission->objective_summary, null, $mission->mission_payload ?? []);
        $segments = $this->decomposeObjective($mission->objective_summary);

        foreach ($segments['objectives'] as $index => $objective) {
            MissionObjective::query()->firstOrCreate(
                [
                    'enterprise_mission_id' => $mission->id,
                    'title' => $objective,
                ],
                [
                    'description' => $objective,
                    'objective_type' => 'initiative',
                    'priority' => $mission->priority,
                    'status' => 'pending',
                    'sequence' => $index + 1,
                ],
            );
        }

        foreach ($segments['phases'] as $index => $phase) {
            MissionPhase::query()->firstOrCreate(
                [
                    'enterprise_mission_id' => $mission->id,
                    'name' => $phase['name'],
                ],
                [
                    'phase_type' => $phase['type'],
                    'status' => 'pending',
                    'sequence' => $index + 1,
                    'owner_team' => $phase['owner_team'],
                    'metadata' => ['generated' => true],
                ],
            );
        }

        foreach ($segments['dependencies'] as $dependency) {
            MissionDependency::query()->firstOrCreate(
                [
                    'enterprise_mission_id' => $mission->id,
                    'source_reference' => $dependency['source_reference'],
                    'target_reference' => $dependency['target_reference'],
                ],
                [
                    'dependency_type' => $dependency['dependency_type'],
                    'status' => 'active',
                ],
            );
        }

        $complexityWeight = str_word_count($mission->objective_summary) + count($basePlan->requiredTools) + count($segments['phases']);
        $durationHours = max(8, $complexityWeight * 1.75);
        $financialCost = round($durationHours * 125, 2);
        $tokenCost = round(($complexityWeight * 900), 2);
        $riskScore = min(95.0, 35.0 + (count($segments['dependencies']) * 6) + ($mission->priority === 'critical' ? 18 : 0));
        $confidence = max(55.0, 92.0 - (count($segments['dependencies']) * 3));

        return MissionPlanVersion::query()->create([
            'enterprise_mission_id' => $mission->id,
            'version_number' => $version,
            'status' => 'planned',
            'complexity' => $complexityWeight > 28 ? 'high' : ($complexityWeight > 16 ? 'medium' : 'low'),
            'estimated_duration_hours' => $durationHours,
            'estimated_financial_cost' => $financialCost,
            'estimated_token_cost' => $tokenCost,
            'tool_requirements' => $basePlan->requiredTools,
            'knowledge_requirements' => ['enterprise_memory', 'knowledge_graph', 'verification_logs'],
            'required_agent_skills' => ['planning', 'execution', 'verification', 'reporting'],
            'confidence_score' => $confidence,
            'risk_score' => $riskScore,
            'compliance_requirements' => ['retention_review', 'approval_trace', 'policy_check'],
            'dependency_map' => $segments['dependencies'],
            'critical_path' => array_column($segments['phases'], 'name'),
            'alternative_plans' => [
                ['mode' => 'cost_optimized', 'focus' => 'Reduce providers and defer non-critical work packages.'],
                ['mode' => 'speed_optimized', 'focus' => 'Increase parallel execution and broaden agent team.'],
            ],
            'plan_payload' => [
                'initiatives' => $segments['objectives'],
                'phases' => $segments['phases'],
                'base_plan_steps' => array_map(fn ($step) => ['title' => $step->title, 'kind' => $step->stepKind], $basePlan->steps),
            ],
            'metadata' => [
                'generated_at' => now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * @return array{objectives: list<string>, phases: list<array{name: string, type: string, owner_team: string}>, dependencies: list<array{dependency_type: string, source_reference: string, target_reference: string}>}
     */
    private function decomposeObjective(string $objective): array
    {
        $parts = preg_split('/[,.]| and /i', $objective) ?: [];
        $objectives = collect($parts)
            ->map(fn ($part): string => trim($part))
            ->filter()
            ->take(5)
            ->values()
            ->all();

        if ($objectives === []) {
            $objectives = [$objective];
        }

        $phases = [
            ['name' => 'Initiatives', 'type' => 'initiative', 'owner_team' => 'Executive Orchestration'],
            ['name' => 'Programmes', 'type' => 'programme', 'owner_team' => 'Planning Office'],
            ['name' => 'Projects', 'type' => 'project', 'owner_team' => 'Execution Team'],
            ['name' => 'Work Packages', 'type' => 'work_package', 'owner_team' => 'Operations Centre'],
            ['name' => 'Tasks', 'type' => 'task', 'owner_team' => 'Agent Teams'],
        ];

        $dependencies = [
            ['dependency_type' => 'critical_path', 'source_reference' => 'Initiatives', 'target_reference' => 'Programmes'],
            ['dependency_type' => 'critical_path', 'source_reference' => 'Programmes', 'target_reference' => 'Projects'],
            ['dependency_type' => 'critical_path', 'source_reference' => 'Projects', 'target_reference' => 'Work Packages'],
            ['dependency_type' => 'critical_path', 'source_reference' => 'Work Packages', 'target_reference' => 'Tasks'],
        ];

        return [
            'objectives' => $objectives,
            'phases' => $phases,
            'dependencies' => $dependencies,
        ];
    }
}
