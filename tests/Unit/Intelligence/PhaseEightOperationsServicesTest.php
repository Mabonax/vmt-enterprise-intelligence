<?php

declare(strict_types=1);

namespace Tests\Unit\Intelligence;

use App\Domains\Intelligence\Operations\Models\EnterpriseMission;
use App\Domains\Intelligence\Operations\Services\KpiCalculationService;
use App\Domains\Intelligence\Operations\Services\MissionPlanner;
use App\Domains\Intelligence\Operations\Services\PredictionEngine;
use App\Domains\Intelligence\Operations\Services\ScenarioSimulator;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhaseEightOperationsServicesTest extends TestCase
{
    use RefreshDatabase;

    public function test_planner_versions_predictions_simulations_and_kpis_are_generated(): void
    {
        $user = User::factory()->create();

        $mission = EnterpriseMission::query()->create([
            'owner_user_id' => $user->id,
            'mission_key' => 'OPS-1001',
            'title' => 'Strategic Operations Mission',
            'objective_summary' => 'Plan and supervise a strategic operations mission with forecasts, approvals, and reporting.',
            'priority' => 'medium',
            'status' => 'draft',
        ]);

        $firstPlan = app(MissionPlanner::class)->plan($mission);
        $secondPlan = app(MissionPlanner::class)->plan($mission);

        $this->assertSame(1, $firstPlan->version_number);
        $this->assertSame(2, $secondPlan->version_number);

        $predictions = app(PredictionEngine::class)->predict($mission);
        $simulation = app(ScenarioSimulator::class)->simulate($mission, 'tool_failure');
        $kpis = app(KpiCalculationService::class)->snapshot();

        $this->assertCount(9, $predictions);
        $this->assertSame('tool_failure', $simulation->scenario_type);
        $this->assertArrayHasKey('mission_success_rate', $kpis->kpi_payload);
    }
}
