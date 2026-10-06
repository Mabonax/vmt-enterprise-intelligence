<?php

declare(strict_types=1);

namespace Tests\Feature\Intelligence;

use App\Domains\Intelligence\Security\Models\GatewayClient;
use App\Domains\Intelligence\Security\Models\GatewayTenant;
use App\Models\User;
use Database\Seeders\IntelligenceRuntimeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class ConnectedApplicationsWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_operator_can_create_tenant_register_client_and_receive_one_time_credential(): void
    {
        $this->seed(IntelligenceRuntimeSeeder::class);

        $user = User::factory()->create();
        $user->givePermissionTo('intelligence.manage');

        $organizationId = (string) Str::uuid();

        DB::table('organizations')->insert([
            'id' => $organizationId,
            'name' => 'VMT ERP Customer',
            'slug' => 'vmt-erp-customer',
            'code' => 'VMT-ERP',
            'status' => 'active',
            'settings' => json_encode([], JSON_UNESCAPED_SLASHES),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($user)
            ->post(route('intelligence.admin-console.connected-applications.tenants.store'), [
                'organization_id' => $organizationId,
                'name' => 'VMT ERP Tenant',
                'slug' => 'vmt-erp-tenant',
            ])
            ->assertRedirect();

        $tenant = GatewayTenant::query()->where('slug', 'vmt-erp-tenant')->firstOrFail();

        $this->actingAs($user)
            ->post(route('intelligence.admin-console.connected-applications.clients.store'), [
                'gateway_tenant_id' => $tenant->getKey(),
                'name' => 'VMT ERP',
                'description' => 'Primary ERP gateway client.',
                'client_type' => 'erp',
                'environment' => 'production',
                'enabled_providers' => ['ollama'],
                'enabled_models' => ['llama3.2:3b'],
                'enabled_capabilities' => ['chat', 'summarise', 'search'],
                'scopes' => ['chat', 'summarise', 'search'],
                'rate_limit_per_minute' => 60,
                'daily_quota' => 100000,
            ])
            ->assertRedirect()
            ->assertSessionHas('gatewayCredential');

        $client = GatewayClient::query()->where('name', 'VMT ERP')->firstOrFail();

        $this->assertSame($tenant->getKey(), $client->gateway_tenant_id);
        $this->assertSame(['ollama'], $client->enabled_providers);
        $this->assertSame(['llama3.2:3b'], $client->enabled_models);
        $this->assertCount(1, $client->credentials()->get());
        $this->assertDatabaseHas('gateway_scopes', [
            'gateway_client_id' => $client->getKey(),
            'scope' => 'chat',
            'status' => 'active',
        ]);
    }

    public function test_user_without_intelligence_management_permission_cannot_open_connected_applications(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('intelligence.admin-console.connected-applications.index'))
            ->assertForbidden();
    }
}
