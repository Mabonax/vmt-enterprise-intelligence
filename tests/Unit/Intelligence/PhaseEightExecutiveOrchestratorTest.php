<?php

declare(strict_types=1);

namespace Tests\Unit\Intelligence;

use App\Domains\Intelligence\Operations\Models\EnterpriseMission;
use App\Domains\Intelligence\Operations\Services\ExecutiveOrchestrator;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhaseEightExecutiveOrchestratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_executive_orchestrator_creates_additive_operations_records(): void
    {
        $user = User::factory()->create();

        $mission = app(ExecutiveOrchestrator::class)->launch($user, [
            'title' => 'Executive Transformation',
            'objective_summary' => 'Transform enterprise service delivery, reporting, and recovery operations.',
            'priority' => 'high',
            'budget_amount' => 120000,
            'mission_payload' => ['sensitive_data' => false],
        ]);

        $this->assertInstanceOf(EnterpriseMission::class, $mission);
        $this->assertDatabaseHas('enterprise_missions', [
            'id' => $mission->id,
            'title' => 'Executive Transformation',
        ]);
        $this->assertDatabaseCount('mission_plan_versions', 1);
        $this->assertDatabaseCount('mission_milestones', 4);
        $this->assertDatabaseCount('mission_risks', 1);
        $this->assertDatabaseCount('mission_decision_records', 1);
        $this->assertDatabaseCount('mission_learning_cycles', 1);
        $this->assertDatabaseCount('enterprise_events', 1);
    }
}
