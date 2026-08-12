<?php

declare(strict_types=1);

namespace Tests\Feature\Intelligence;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhaseEightWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_phase_eight_workspace_pages_render_without_replacing_earlier_workspace_routes(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('intelligence.operations.executive-dashboard'))
            ->assertOk()
            ->assertSee('Executive Dashboard');

        $this->actingAs($user)
            ->get(route('intelligence.operations.enterprise-missions'))
            ->assertOk()
            ->assertSee('Enterprise Missions');

        $this->actingAs($user)
            ->get(route('intelligence.dashboard'))
            ->assertOk()
            ->assertSee('Intelligence Dashboard');
    }
}
