<?php

declare(strict_types=1);

namespace Tests\Feature\Intelligence;

use App\Domains\Intelligence\Agents\Models\AgentApproval;
use App\Domains\Intelligence\Models\Agent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PhaseSevenApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_multi_agent_api_can_create_agent_and_start_execution(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson(route('api.intelligence.agents.store'), [
            'name' => 'Enterprise Planning Lead',
            'slug' => 'enterprise-planning-lead',
            'agent_role_key' => 'planning_agent',
            'visibility' => 'organization',
        ])->assertCreated()
            ->assertJsonPath('agent.slug', 'enterprise-planning-lead');

        $agent = Agent::query()->where('slug', 'enterprise-planning-lead')->firstOrFail();

        $this->postJson(route('api.intelligence.start'), [
            'agent_id' => $agent->id,
            'title' => 'Quarterly Intelligence Sweep',
            'objective' => 'Research verification workflow and produce an executive summary.',
            'execution_mode' => 'immediate',
        ])->assertCreated()
            ->assertJson(fn ($json) => $json
                ->where('session.title', 'Quarterly Intelligence Sweep')
                ->whereType('session.status', 'string')
                ->etc()
            );

        $this->assertDatabaseHas('agent_sessions', [
            'agent_id' => $agent->id,
            'title' => 'Quarterly Intelligence Sweep',
        ]);
    }

    public function test_multi_agent_execution_can_pause_for_approval_and_resume_after_approval(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $agent = Agent::query()->create([
            'owner_user_id' => $user->id,
            'organization_id' => $user->organization_id,
            'name' => 'Compliance Lead',
            'slug' => 'compliance-lead',
            'status' => 'active',
            'visibility' => 'organization',
            'memory_enabled' => true,
            'agent_role_key' => 'compliance_agent',
            'reasoning_style' => 'policy',
            'risk_tolerance' => 'low',
            'verification_strategy' => 'reviewer',
            'memory_scope' => 'shared',
            'delegation_enabled' => true,
            'approval_required' => true,
        ]);

        $response = $this->postJson(route('api.intelligence.start'), [
            'agent_id' => $agent->id,
            'title' => 'Compliance Review',
            'objective' => 'Assess compliance workflow and summarize risk.',
            'execution_mode' => 'immediate',
            'approval_role' => 'manager',
        ])->assertCreated();

        $sessionId = (string) $response->json('session.id');

        $this->assertDatabaseHas('agent_sessions', [
            'id' => $sessionId,
            'status' => 'paused',
        ]);

        $approval = AgentApproval::query()->where('agent_session_id', $sessionId)->firstOrFail();

        $this->postJson(route('api.intelligence.approvals.approve', $approval), [
            'notes' => 'Approved for continuation.',
        ])->assertOk()
            ->assertJsonPath('approval.status', 'approved');
    }
}
