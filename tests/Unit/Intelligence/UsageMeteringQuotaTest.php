<?php

declare(strict_types=1);

namespace Tests\Unit\Intelligence;

use App\Domains\Intelligence\Commercial\DTOs\UsageMeterRecordData;
use App\Domains\Intelligence\Commercial\Models\IntelligencePackage;
use App\Domains\Intelligence\Commercial\Models\IntelligenceTenant;
use App\Domains\Intelligence\Commercial\Services\SubscriptionService;
use App\Domains\Intelligence\Commercial\Services\UsageMeteringService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UsageMeteringQuotaTest extends TestCase
{
    use RefreshDatabase;

    public function test_usage_metering_warns_when_quota_is_nearly_reached(): void
    {
        $tenant = IntelligenceTenant::query()->create([
            'name' => 'Usage Tenant',
            'slug' => 'usage-tenant',
            'status' => 'active',
        ]);

        $package = IntelligencePackage::query()->create([
            'name' => 'Usage Package',
            'slug' => 'usage-package',
            'tier' => 'starter_intelligence',
            'monthly_execution_limit' => 100,
            'token_usage_limit' => 5000,
            'knowledge_storage_limit_mb' => 1024,
            'connector_limit' => 100,
            'deployment_mode_eligibility' => ['shared_saas'],
            'enabled_agent_capabilities' => ['agent_executions'],
        ]);

        $subscription = app(SubscriptionService::class)->create($tenant, $package);
        $result = app(UsageMeteringService::class)->record($subscription->id, new UsageMeterRecordData('agent_executions', 90));

        $this->assertTrue($result['warning']);
        $this->assertFalse($result['hard_blocked']);
    }
}