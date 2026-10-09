<?php

declare(strict_types=1);

namespace Tests\Feature\Intelligence;

use App\Domains\Intelligence\Security\Models\GatewayClient;
use App\Domains\Intelligence\Security\Models\GatewayTenant;
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
}
