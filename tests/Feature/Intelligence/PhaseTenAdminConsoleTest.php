<?php

declare(strict_types=1);

namespace Tests\Feature\Intelligence;

use App\Models\User;
use Database\Seeders\IntelligenceAdminConsoleSeeder;
use Database\Seeders\IntelligenceCommercialSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PhaseTenAdminConsoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_phase_ten_admin_console_routes_load(): void
    {
        $this->seed([
            IntelligenceCommercialSeeder::class,
            IntelligenceAdminConsoleSeeder::class,
        ]);

        $user = User::factory()->create();
        $user->givePermissionTo('intelligence.admin_console.view');

        $this->actingAs($user)
            ->get(route('intelligence.admin-console.index'))
            ->assertOk()
            ->assertSee('Command Center');

        $this->actingAs($user)
            ->get(route('intelligence.admin-console.executive'))
            ->assertOk()
            ->assertSee('Executive View');
    }

    public function test_phase_ten_admin_console_api_endpoints_return_successful_payloads(): void
    {
        $this->seed([
            IntelligenceCommercialSeeder::class,
            IntelligenceAdminConsoleSeeder::class,
        ]);

        $user = User::factory()->create();
        $user->givePermissionTo([
            'intelligence.admin_console.view',
            'intelligence.admin_console.health.view',
            'intelligence.admin_console.readiness.view',
            'intelligence.admin_console.audit.view',
        ]);
        Sanctum::actingAs($user);

        $this->getJson(route('api.intelligence.admin-console.dashboard'))
            ->assertOk()
            ->assertJsonStructure(['dashboard', 'metrics', 'widgets', 'alerts', 'actions', 'health', 'readiness']);

        $this->getJson(route('api.intelligence.admin-console.metrics'))
            ->assertOk()
            ->assertJsonStructure(['metrics']);

        $this->getJson(route('api.intelligence.admin-console.health'))
            ->assertOk()
            ->assertJsonStructure(['health' => ['overall_status', 'score', 'signals', 'recommendations']]);
    }
}
