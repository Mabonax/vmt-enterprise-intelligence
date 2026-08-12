<?php

declare(strict_types=1);

namespace Tests\Feature\Intelligence;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhaseSevenWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_phase_seven_workspace_pages_render(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('intelligence.enterprise-agents'))
            ->assertOk()
            ->assertSee('Enterprise Agents');

        $this->actingAs($user)
            ->get(route('intelligence.monitoring'))
            ->assertOk()
            ->assertSee('Monitoring');
    }
}
