<?php

declare(strict_types=1);

namespace Tests\Feature\Intelligence;

use App\Models\User;
use Database\Seeders\AdministratorSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavigationRenderingTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_users_can_open_intelligence_workspace_pages(): void
    {
        $this->seed(AdministratorSeeder::class);

        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('intelligence.dashboard'))
            ->assertOk()
            ->assertSee('Intelligence Dashboard');

        $this->actingAs($user)
            ->get(route('intelligence.conversations'))
            ->assertOk()
            ->assertSee('Conversations');
    }
}
