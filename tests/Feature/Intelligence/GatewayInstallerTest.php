<?php

declare(strict_types=1);

namespace Tests\Feature\Intelligence;

use App\Domains\Intelligence\Security\Models\GatewayClient;
use App\Domains\Intelligence\Security\Models\GatewayTenant;
use App\Domains\Intelligence\Security\Services\GatewayClientProvisioningService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class GatewayInstallerTest extends TestCase
{
    use RefreshDatabase;

    public function test_check_inspects_requirements_without_creating_clients(): void
    {
        $this->artisan('gateway:install', ['--check' => true])->assertExitCode(0);
        $this->assertDatabaseCount('gateway_clients', 0);
    }

    public function test_provision_requires_valid_organization(): void
    {
        $this->artisan('gateway:install', [
            '--provision' => true,
            '--organization-id' => (string) Str::uuid(),
            '--erp-name' => 'GPERP',
        ])->assertExitCode(2);
    }

    public function test_provisioning_is_idempotent_and_issues_one_credential(): void
    {
        $id = (string) Str::uuid();
        DB::table('organizations')->insert([
            'id' => $id,
            'name' => 'Dedicated Client',
            'slug' => 'dedicated-client',
            'code' => 'DEDICATED',
            'status' => 'active',
            'settings' => json_encode([]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $args = ['--provision' => true, '--organization-id' => $id, '--erp-name' => 'GPERP'];

        $this->artisan('gateway:install', $args)->assertExitCode(0);
        $this->artisan('gateway:install', $args)->assertExitCode(0);

        $this->assertSame(1, GatewayTenant::query()->where('organization_id', $id)->count());
        $client = GatewayClient::query()->where('organization_id', $id)->firstOrFail();
        $this->assertSame('GPERP', $client->name);
        $this->assertSame(1, $client->credentials()->count());
    }

    public function test_provision_reuses_operator_client_without_creating_another_tenant_or_credentials(): void
    {
        $id = (string) Str::uuid();
        DB::table('organizations')->insert([
            'id' => $id, 'name' => 'GPERP', 'slug' => 'gperp', 'code' => 'GPERP',
            'status' => 'active', 'settings' => '{}', 'created_at' => now(), 'updated_at' => now(),
        ]);
        $tenant = GatewayTenant::query()->create([
            'organization_id' => $id, 'name' => 'Operator tenant', 'slug' => 'operator-tenant', 'status' => 'active',
        ]);
        $client = app(GatewayClientProvisioningService::class)->createClient([
            'gateway_tenant_id' => $tenant->id, 'organization_id' => $id, 'name' => 'GPERP',
            'enabled_providers' => ['ollama'], 'enabled_models' => ['llama3.2:3b'],
            'enabled_capabilities' => ['summarise'], 'scopes' => ['summarise'],
        ]);
        $this->artisan('gateway:install', ['--provision' => true, '--organization-id' => $id, '--erp-name' => 'GPERP'])
            ->assertExitCode(0);
        $this->assertDatabaseCount('gateway_tenants', 1);
        $this->assertDatabaseCount('gateway_clients', 1);
        $this->assertSame(0, $client->credentials()->count());
    }

    public function test_inactive_operator_client_cannot_create_duplicate_installation_records(): void
    {
        $id = (string) Str::uuid();
        DB::table('organizations')->insert([
            'id' => $id, 'name' => 'GPERP', 'slug' => 'gperp', 'code' => 'GPERP',
            'status' => 'active', 'settings' => '{}', 'created_at' => now(), 'updated_at' => now(),
        ]);
        $tenant = GatewayTenant::query()->create([
            'organization_id' => $id, 'name' => 'Operator tenant', 'slug' => 'operator-tenant', 'status' => 'active',
        ]);
        GatewayClient::query()->create([
            'gateway_tenant_id' => $tenant->id, 'organization_id' => $id, 'name' => 'GPERP', 'status' => 'disabled',
        ]);
        $this->artisan('gateway:install', ['--provision' => true, '--organization-id' => $id, '--erp-name' => 'GPERP'])->assertExitCode(2);
        $this->assertDatabaseCount('gateway_tenants', 1);
        $this->assertDatabaseCount('gateway_clients', 1);
        $this->assertDatabaseCount('gateway_api_credentials', 0);
    }
}
