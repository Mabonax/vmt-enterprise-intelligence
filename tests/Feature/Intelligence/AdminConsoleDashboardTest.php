<?php

declare(strict_types=1);

namespace Tests\Feature\Intelligence;

use App\Models\User;
use Database\Seeders\IntelligenceAdminConsoleSeeder;
use Database\Seeders\IntelligenceCommercialSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminConsoleDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_payload_returns_expected_keys(): void
    {
        $this->seed([
            IntelligenceCommercialSeeder::class,
            IntelligenceAdminConsoleSeeder::class,
        ]);

        $user = User::factory()->create();
        $user->givePermissionTo('intelligence.admin_console.view');
        Sanctum::actingAs($user);

        $this->getJson(route('api.intelligence.admin-console.dashboard'))
            ->assertOk()
            ->assertJsonStructure([
                'dashboard' => ['name', 'slug', 'description', 'audience_type'],
                'metrics' => [
                    'total_commercial_packages',
                    'active_tenants',
                    'pending_tenant_provisions',
                    'deployment_runs_by_status',
                    'open_support_cases',
                    'usage_quota_pressure',
                    'operations_mission_health',
                    'agent_execution_health',
                    'knowledge_ingestion_health',
                    'compliance_policy_warnings',
                ],
                'widgets',
                'alerts',
                'actions',
                'health',
                'readiness',
            ]);
    }
}
