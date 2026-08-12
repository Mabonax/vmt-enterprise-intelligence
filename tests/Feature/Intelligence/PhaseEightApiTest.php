<?php

declare(strict_types=1);

namespace Tests\Feature\Intelligence;

use App\Domains\Intelligence\Operations\Models\EnterpriseMission;
use App\Domains\Intelligence\Operations\Models\MissionApproval;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PhaseEightApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_operations_api_can_create_plan_execute_predict_and_simulate_a_mission(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson(route('api.intelligence.operations.missions.store'), [
            'title' => 'Enterprise Revenue Expansion',
            'objective_summary' => 'Expand revenue operations and coordinate forecasting, sales enablement, and executive reporting.',
            'priority' => 'critical',
            'budget_amount' => 85000,
            'mission_payload' => [
                'sensitive_data' => false,
            ],
        ])->assertCreated()
            ->assertJsonPath('mission.title', 'Enterprise Revenue Expansion');

        $mission = EnterpriseMission::query()->findOrFail((string) $response->json('mission.id'));

        $this->postJson(route('api.intelligence.operations.missions.plan', $mission))
            ->assertOk()
            ->assertJsonPath('plan.version_number', 2);

        $this->postJson(route('api.intelligence.operations.missions.execute', $mission), [
            'mode' => 'autonomous',
        ])->assertOk()
            ->assertJsonPath('mission.status', 'active');

        $this->getJson(route('api.intelligence.operations.predictions', $mission))
            ->assertOk()
            ->assertJsonCount(9, 'predictions');

        $this->postJson(route('api.intelligence.operations.simulations.store', $mission), [
            'scenario_type' => 'provider_outage',
        ])->assertOk()
            ->assertJsonPath('simulation.scenario_type', 'provider_outage');

        $this->postJson(route('api.intelligence.operations.decisions.store', $mission), [
            'decision_key' => 'provider-strategy',
            'reasoning' => 'Choose the fastest healthy provider for the first execution wave.',
            'chosen_option' => 'primary-provider',
        ])->assertCreated()
            ->assertJsonPath('decision.decision_key', 'provider-strategy');

        $this->getJson(route('api.intelligence.operations.kpis'))
            ->assertOk()
            ->assertJsonStructure(['kpis' => ['kpi_payload']]);
    }

    public function test_operations_api_handles_approval_bound_execution_and_approval_resolution(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson(route('api.intelligence.operations.missions.store'), [
            'title' => 'Sensitive Governance Mission',
            'objective_summary' => 'Coordinate high-budget governance review and compliance-sensitive remediation programme.',
            'priority' => 'critical',
            'budget_amount' => 300000,
            'require_approval' => true,
            'mission_payload' => [
                'sensitive_data' => true,
            ],
        ])->assertCreated();

        $mission = EnterpriseMission::query()->findOrFail((string) $response->json('mission.id'));

        $this->postJson(route('api.intelligence.operations.missions.execute', $mission))
            ->assertOk()
            ->assertJsonPath('mission.status', 'awaiting_approval');

        $approval = MissionApproval::query()->where('enterprise_mission_id', $mission->id)->firstOrFail();

        $this->postJson(route('api.intelligence.operations.approvals.approve', $approval), [
            'notes' => 'Executive approval granted.',
        ])->assertOk()
            ->assertJsonPath('approval.status', 'approved');

        $this->getJson(route('api.intelligence.operations.compliance', $mission))
            ->assertOk()
            ->assertJsonPath('compliance.0.status', 'conditional');

        $this->getJson(route('api.intelligence.operations.policies', $mission))
            ->assertOk()
            ->assertJsonPath('policies.0.status', 'review_required');
    }
}
