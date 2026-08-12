<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Commercial\Services;

use App\Domains\Intelligence\Commercial\Models\BillingAccount;
use App\Domains\Intelligence\Commercial\Models\IntelligencePackage;
use App\Domains\Intelligence\Commercial\Models\IntelligenceSubscription;
use App\Domains\Intelligence\Commercial\Models\IntelligenceTenant;
use App\Domains\Intelligence\Commercial\Models\UsageMeter;
use App\Domains\Intelligence\Commercial\Models\UsageQuota;

class SubscriptionService
{
    public function create(IntelligenceTenant $tenant, IntelligencePackage $package): IntelligenceSubscription
    {
        $billingAccount = BillingAccount::query()->firstOrCreate(
            ['intelligence_tenant_id' => $tenant->id, 'account_name' => $tenant->name.' Billing'],
            [
                'billing_email' => $tenant->primary_contact_email,
                'currency' => 'ZAR',
                'status' => 'ready',
                'address_payload' => [],
                'metadata' => [],
            ],
        );

        $subscription = IntelligenceSubscription::query()->create([
            'intelligence_tenant_id' => $tenant->id,
            'intelligence_package_id' => $package->id,
            'billing_account_id' => $billingAccount->id,
            'status' => 'active',
            'starts_at' => now(),
            'renews_at' => now()->addMonth(),
            'monthly_price' => (float) ($package->pricingRules()->first()?->base_price ?? 0),
            'currency' => 'ZAR',
            'metadata' => ['package_tier' => $package->tier],
        ]);

        foreach ([
            'agent_executions' => (float) ($package->monthly_execution_limit ?? 500),
            'token_usage' => (float) ($package->token_usage_limit ?? 100000),
            'knowledge_storage' => (float) ($package->knowledge_storage_limit_mb ?? 2048),
            'connector_calls' => (float) ($package->connector_limit ?? 5000),
        ] as $key => $limit) {
            UsageMeter::query()->create([
                'intelligence_subscription_id' => $subscription->id,
                'meter_key' => $key,
                'label' => str_replace('_', ' ', $key),
                'usage_total' => 0,
                'usage_limit' => $limit,
                'warning_threshold' => 80,
                'hard_limit' => false,
                'period_started_at' => now()->startOfMonth(),
                'period_ends_at' => now()->endOfMonth(),
                'metadata' => [],
            ]);

            UsageQuota::query()->create([
                'intelligence_subscription_id' => $subscription->id,
                'quota_key' => $key,
                'quota_limit' => $limit,
                'consumed' => 0,
                'warning_state' => false,
                'enforcement_mode' => 'soft',
                'metadata' => [],
            ]);
        }

        return $subscription->fresh(['meters', 'quotas']);
    }
}