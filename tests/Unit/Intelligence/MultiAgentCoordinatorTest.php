<?php

declare(strict_types=1);

namespace Tests\Unit\Intelligence;

use App\Domains\Intelligence\Agents\DTOs\EnterpriseAgentExecutionData;
use App\Domains\Intelligence\Agents\Models\AgentSession;
use App\Domains\Intelligence\Agents\Services\MultiAgentCoordinator;
use App\Domains\Intelligence\Models\Agent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiAgentCoordinatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_coordinator_can_create_a_queued_session(): void
    {
        $user = User::factory()->create();
        $agent = Agent::query()->create([
            'owner_user_id' => $user->id,
            'organization_id' => $user->organization_id,
            'name' => 'Workflow Lead',
            'slug' => 'workflow-lead',
            'status' => 'active',
            'visibility' => 'organization',
            'memory_enabled' => true,
            'agent_role_key' => 'workflow_agent',
            'reasoning_style' => 'orchestration',
            'risk_tolerance' => 'moderate',
            'verification_strategy' => 'reviewer',
            'memory_scope' => 'shared',
            'delegation_enabled' => true,
            'approval_required' => false,
        ]);

        $session = app(MultiAgentCoordinator::class)->startExecution(
            $user,
            $agent,
            new EnterpriseAgentExecutionData(
                objective: 'Plan and queue an enterprise workflow review.',
                title: 'Workflow Review',
            ),
        );

        $this->assertInstanceOf(AgentSession::class, $session);
        $this->assertSame('queued', $session->status);
    }
}
