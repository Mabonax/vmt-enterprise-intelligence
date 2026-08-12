<?php

declare(strict_types=1);

namespace Tests\Feature\Intelligence;

use App\Models\User;
use Database\Seeders\AdministratorSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_renders_runtime_copy(): void
    {
        $this->seed(AdministratorSeeder::class);

        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Enterprise intelligence runtime')
            ->assertSee('Conversation persistence');
    }
}
