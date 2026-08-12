<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\AdministratorSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlatformAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_requires_authentication(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_open_scaffolded_pages(): void
    {
        $this->seed(AdministratorSeeder::class);

        $user = User::factory()->create();
        $this->actingAs($user)
            ->get(route('platform.gateway'))
            ->assertOk()
            ->assertSee('Gateway');
    }
}
