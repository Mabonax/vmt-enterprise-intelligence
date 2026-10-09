<?php

declare(strict_types=1);

namespace Tests\Feature\Intelligence;

use App\Models\User;
use Database\Seeders\IntelligenceCommercialSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class IntelligenceCommercialDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_legacy_commercial_routes_are_not_available_in_dedicated_mode(): void
    {
        config()->set('deployment.commercial_console_enabled', false);
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->get('/intelligence/commercial/packages')->assertNotFound();
        $this->getJson('/api/intelligence/commercial/packages')->assertNotFound();
        $this->postJson('/api/intelligence/commercial/subscriptions', [])->assertNotFound();
    }

    public function test_workspace_pages_and_api_endpoints_render_for_commercial_intelligence(): void
    {
        config()->set('deployment.commercial_console_enabled', true);
        $user = User::factory()->create();
        $this->seed(IntelligenceCommercialSeeder::class);

        $this->actingAs($user)->get('/intelligence/commercial/packages')->assertOk()->assertSee('Commercial Packages');
        $this->actingAs($user)->get('/intelligence/commercial/tenants')->assertOk()->assertSee('Commercial Tenants');
        $this->actingAs($user)->get('/intelligence/commercial/readiness')->assertOk()->assertSee('Release Readiness');

        Sanctum::actingAs($user);
        $this->getJson('/api/intelligence/commercial/packages')->assertOk()->assertJsonStructure(['packages']);
        $this->getJson('/api/intelligence/commercial/support')->assertOk()->assertJsonStructure(['tickets', 'sla']);
        $this->getJson('/api/intelligence/commercial/readiness')->assertOk()->assertJsonStructure(['checks']);
    }
}