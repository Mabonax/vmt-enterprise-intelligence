<?php

declare(strict_types=1);

namespace Tests\Unit\Intelligence;

use App\Domains\Intelligence\Commercial\Models\IntelligencePackage;
use App\Domains\Intelligence\Commercial\Models\PackageEntitlement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommercialPackageEntitlementTest extends TestCase
{
    use RefreshDatabase;

    public function test_package_creation_can_persist_entitlements_and_module_access(): void
    {
        $package = IntelligencePackage::query()->create([
            'name' => 'Test Package',
            'slug' => 'test-package',
            'tier' => 'starter_intelligence',
            'deployment_mode_eligibility' => ['shared_saas'],
            'enabled_agent_capabilities' => ['agent_executions'],
            'is_active' => true,
        ]);

        PackageEntitlement::query()->create([
            'intelligence_package_id' => $package->id,
            'entitlement_key' => 'support',
            'label' => 'Support',
            'status' => 'enabled',
        ]);

        $this->assertSame(1, $package->entitlements()->count());
        $this->assertSame('support', $package->entitlements()->firstOrFail()->entitlement_key);
    }
}