<?php

declare(strict_types=1);

namespace Tests\Feature\Intelligence;

use App\Domains\Intelligence\Commercial\Models\IntelligencePackage;
use App\Domains\Intelligence\Commercial\Models\IntelligenceTenant;
use App\Domains\Intelligence\Commercial\Services\TenantProvisioningService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantProvisioningWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_provisioning_progresses_into_active_state_and_builds_workspace(): void
    {
        $user = User::factory()->create();
        $tenant = IntelligenceTenant::query()->create([
            'name' => 'Provisioning Tenant',
            'slug' => 'provisioning-tenant',
            'status' => 'draft',
        ]);
        $package = IntelligencePackage::query()->create([
            'name' => 'Provisioning Package',
            'slug' => 'provisioning-package',
            'tier' => 'professional_intelligence',
            'deployment_mode_eligibility' => ['shared_saas'],
            'enabled_agent_capabilities' => ['agent_executions'],
        ]);

        $service = app(TenantProvisioningService::class);
        $request = $service->submit($user, $tenant, $package, ['deployment_mode' => 'shared_saas']);
        $result = $service->approve($request);

        $this->assertSame('active', $result->status);
        $this->assertDatabaseHas('tenant_workspaces', ['intelligence_tenant_id' => $tenant->id]);
        $this->assertDatabaseHas('tenant_security_profiles', ['intelligence_tenant_id' => $tenant->id]);
    }
}