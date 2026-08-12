<?php

declare(strict_types=1);

namespace Tests\Unit\Intelligence;

use App\Domains\Intelligence\Models\Agent;
use App\Domains\Intelligence\Services\PlanningEngine;
use Database\Seeders\IntelligenceRuntimeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanningEngineTest extends TestCase
{
    use RefreshDatabase;

    public function test_planner_builds_dynamic_tool_and_workflow_steps(): void
    {
        $this->seed(IntelligenceRuntimeSeeder::class);

        $agent = Agent::query()->where('slug', 'executive-assistant')->firstOrFail();
        $plan = app(PlanningEngine::class)->plan(
            'Generate a platform status summary and schedule follow-up actions.',
            $agent,
            ['memory_count' => 2, 'organization_id' => 'org-1'],
        );

        $this->assertNotEmpty($plan->requiredTools);
        $this->assertSame('planned', $plan->completionState);
        $this->assertContains('verification', $plan->dependencies);
        $this->assertContains('workflow', $plan->dependencies);
        $this->assertTrue(collect($plan->steps)->contains(fn ($step) => $step->stepKind === 'workflow'));
        $this->assertTrue(collect($plan->steps)->contains(fn ($step) => $step->toolSlug === 'platform_status'));
    }
}
