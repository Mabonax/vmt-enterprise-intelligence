<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domains\Intelligence\Commercial\Models\BillingContact;
use App\Domains\Intelligence\Commercial\Models\IntelligencePackage;
use App\Domains\Intelligence\Commercial\Models\IntelligenceTenant;
use App\Domains\Intelligence\Commercial\Models\PackageEntitlement;
use App\Domains\Intelligence\Commercial\Models\PackageFeature;
use App\Domains\Intelligence\Commercial\Models\PackageLimit;
use App\Domains\Intelligence\Commercial\Models\PackageModuleAccess;
use App\Domains\Intelligence\Commercial\Models\PackagePricingRule;
use App\Domains\Intelligence\Commercial\Models\PackageUpgradePath;
use App\Domains\Intelligence\Commercial\Services\DeploymentRunbookService;
use App\Domains\Intelligence\Commercial\Services\IntelligenceProposalBuilder;
use App\Domains\Intelligence\Commercial\Services\MaintenancePlanner;
use App\Domains\Intelligence\Commercial\Services\ReleaseReadinessService;
use App\Domains\Intelligence\Commercial\Services\SubscriptionService;
use App\Domains\Intelligence\Commercial\Services\SupportTicketService;
use App\Domains\Intelligence\Commercial\Services\TenantProvisioningService;
use App\Models\User;
use Illuminate\Database\Seeder;

class IntelligenceCommercialSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::query()->first() ?? User::factory()->create([
            'name' => 'Commercial Operator',
            'email' => 'commercial@vmt.local',
        ]);

        $packages = collect([
            ['name' => 'Starter Intelligence', 'slug' => 'starter-intelligence', 'tier' => 'starter_intelligence', 'price' => 8500, 'executions' => 500, 'tokens' => 150000, 'storage' => 4096, 'members' => 10, 'connectors' => 5],
            ['name' => 'Professional Intelligence', 'slug' => 'professional-intelligence', 'tier' => 'professional_intelligence', 'price' => 18500, 'executions' => 2500, 'tokens' => 750000, 'storage' => 12288, 'members' => 30, 'connectors' => 12],
            ['name' => 'Enterprise Intelligence', 'slug' => 'enterprise-intelligence', 'tier' => 'enterprise_intelligence', 'price' => 42000, 'executions' => 10000, 'tokens' => 3000000, 'storage' => 51200, 'members' => 100, 'connectors' => 30],
            ['name' => 'Sovereign Intelligence', 'slug' => 'sovereign-intelligence', 'tier' => 'sovereign_intelligence', 'price' => 78000, 'executions' => 18000, 'tokens' => 6000000, 'storage' => 102400, 'members' => 180, 'connectors' => 45],
            ['name' => 'Government / NGO Intelligence', 'slug' => 'government-ngo-intelligence', 'tier' => 'government_ngo_intelligence', 'price' => 36500, 'executions' => 8000, 'tokens' => 2500000, 'storage' => 40960, 'members' => 80, 'connectors' => 18],
            ['name' => 'Healthcare Intelligence', 'slug' => 'healthcare-intelligence', 'tier' => 'healthcare_intelligence', 'price' => 52000, 'executions' => 9500, 'tokens' => 3200000, 'storage' => 61440, 'members' => 120, 'connectors' => 24],
            ['name' => 'Education Intelligence', 'slug' => 'education-intelligence', 'tier' => 'education_intelligence', 'price' => 22500, 'executions' => 4000, 'tokens' => 1250000, 'storage' => 20480, 'members' => 50, 'connectors' => 10],
            ['name' => 'Custom Managed Intelligence', 'slug' => 'custom-managed-intelligence', 'tier' => 'custom_managed_intelligence', 'price' => 95000, 'executions' => 25000, 'tokens' => 10000000, 'storage' => 204800, 'members' => 250, 'connectors' => 60],
        ])->map(function (array $definition): IntelligencePackage {
            $package = IntelligencePackage::query()->updateOrCreate(
                ['slug' => $definition['slug']],
                [
                    'name' => $definition['name'],
                    'tier' => $definition['tier'],
                    'description' => $definition['name'].' package for enterprise commercialization.',
                    'support_level' => $definition['price'] >= 50000 ? 'premium' : 'standard',
                    'sla_level' => $definition['price'] >= 50000 ? '24x7' : 'business_hours',
                    'deployment_mode_eligibility' => ['shared_saas', 'dedicated_saas', 'private_cloud', 'on_premise'],
                    'enabled_agent_capabilities' => ['agent_executions', 'multi_agent_teams', 'knowledge_ingestion', 'prediction_runs'],
                    'monthly_execution_limit' => $definition['executions'],
                    'token_usage_limit' => $definition['tokens'],
                    'knowledge_storage_limit_mb' => $definition['storage'],
                    'team_member_limit' => $definition['members'],
                    'approval_workflow_limit' => 25,
                    'connector_limit' => $definition['connectors'],
                    'is_active' => true,
                    'metadata' => [],
                ],
            );

            PackageFeature::query()->updateOrCreate(
                ['intelligence_package_id' => $package->id, 'feature_key' => 'multi_agent_runtime'],
                ['name' => 'Multi-agent runtime', 'description' => 'Provider-neutral enterprise intelligence agents.', 'is_enabled' => true, 'metadata' => []],
            );
            PackageFeature::query()->updateOrCreate(
                ['intelligence_package_id' => $package->id, 'feature_key' => 'knowledge_platform'],
                ['name' => 'Knowledge platform', 'description' => 'Enterprise knowledge ingestion and retrieval.', 'is_enabled' => true, 'metadata' => []],
            );

            foreach ([
                'monthly_executions' => $definition['executions'],
                'token_usage' => $definition['tokens'],
                'knowledge_storage_mb' => $definition['storage'],
                'team_members' => $definition['members'],
                'connector_calls' => $definition['connectors'],
            ] as $key => $value) {
                PackageLimit::query()->updateOrCreate(
                    ['intelligence_package_id' => $package->id, 'limit_key' => $key],
                    ['limit_value' => $value, 'unit' => 'count', 'is_hard_limit' => false, 'metadata' => []],
                );
            }

            foreach (['intelligence_dashboard', 'knowledge', 'enterprise_agents', 'operations', 'commercial'] as $module) {
                PackageModuleAccess::query()->updateOrCreate(
                    ['intelligence_package_id' => $package->id, 'module_key' => $module],
                    ['access_level' => 'full', 'metadata' => []],
                );
            }

            foreach (['support', 'deployment', 'billing'] as $entitlement) {
                PackageEntitlement::query()->updateOrCreate(
                    ['intelligence_package_id' => $package->id, 'entitlement_key' => $entitlement],
                    ['label' => ucwords($entitlement), 'status' => 'enabled', 'metadata' => []],
                );
            }

            PackagePricingRule::query()->updateOrCreate(
                ['intelligence_package_id' => $package->id, 'billing_frequency' => 'monthly'],
                ['currency' => 'ZAR', 'base_price' => $definition['price'], 'overage_policy' => 'metered_soft_limit', 'metadata' => []],
            );

            return $package;
        });

        for ($index = 0; $index < $packages->count() - 1; $index++) {
            PackageUpgradePath::query()->updateOrCreate(
                [
                    'from_package_id' => $packages[$index]->id,
                    'to_package_id' => $packages[$index + 1]->id,
                ],
                [
                    'path_label' => $packages[$index]->name.' to '.$packages[$index + 1]->name,
                    'requirements' => ['commercial_review'],
                    'is_active' => true,
                    'metadata' => [],
                ],
            );
        }

        $tenants = collect([
            ['name' => 'VMT Internal Tenant', 'slug' => 'vmt-internal', 'industry' => 'Internal', 'email' => 'platform@vmt.local', 'mode' => 'private_cloud', 'package' => 'enterprise-intelligence'],
            ['name' => 'Metro Municipality Demo', 'slug' => 'metro-municipality-demo', 'industry' => 'Municipality', 'email' => 'cio@metro.example', 'mode' => 'sovereign_self_hosted', 'package' => 'government-ngo-intelligence'],
            ['name' => 'Healthcare Intelligence Demo', 'slug' => 'healthcare-demo', 'industry' => 'Healthcare', 'email' => 'ops@healthcare.example', 'mode' => 'on_premise', 'package' => 'healthcare-intelligence'],
            ['name' => 'Education NPC Demo', 'slug' => 'education-npc-demo', 'industry' => 'Education', 'email' => 'director@education.example', 'mode' => 'shared_saas', 'package' => 'education-intelligence'],
        ])->map(function (array $definition) use ($owner, $packages): IntelligenceTenant {
            $tenant = IntelligenceTenant::query()->updateOrCreate(
                ['slug' => $definition['slug']],
                [
                    'name' => $definition['name'],
                    'industry' => $definition['industry'],
                    'status' => 'draft',
                    'deployment_mode' => $definition['mode'],
                    'primary_contact_name' => $definition['name'].' Contact',
                    'primary_contact_email' => $definition['email'],
                    'package_snapshot' => [],
                    'metadata' => [],
                ],
            );

            $package = $packages->firstWhere('slug', $definition['package']);

            if (! $tenant->provisioningRequests()->exists()) {
                app(TenantProvisioningService::class)->submit($owner, $tenant, $package, ['deployment_mode' => $definition['mode']]);
            }

            $request = $tenant->provisioningRequests()->latest()->first();

            if ($request !== null && $tenant->status !== 'active') {
                app(TenantProvisioningService::class)->approve($request);
            }

            $subscription = $tenant->subscriptions()
                ->where('intelligence_package_id', $package->id)
                ->latest()
                ->first() ?? app(SubscriptionService::class)->create($tenant, $package);

            BillingContact::query()->updateOrCreate(
                ['billing_account_id' => $subscription->billing_account_id, 'email' => $definition['email']],
                ['name' => $definition['name'].' Finance', 'phone' => '+27-11-000-0000', 'role' => 'finance', 'is_primary' => true, 'metadata' => []],
            );

            $proposal = $tenant->intelligenceProposals()->latest()->first() ?? app(IntelligenceProposalBuilder::class)->build($tenant, $package, $definition['mode']);

            if (! $tenant->deploymentRunbooks()->exists()) {
                app(DeploymentRunbookService::class)->create($tenant, $proposal->id);
            }

            if (! $tenant->supportTickets()->where('title', $tenant->name.' onboarding support request')->exists()) {
                app(SupportTicketService::class)->open($tenant, $tenant->name.' onboarding support request', 'medium');
            }

            if (! $tenant->maintenanceWindows()->where('title', $tenant->name.' monthly patch window')->exists()) {
                app(MaintenancePlanner::class)->schedule($tenant, $tenant->name.' monthly patch window');
            }

            if (! $tenant->releaseReadinessChecks()->exists()) {
                app(ReleaseReadinessService::class)->check($tenant);
            }

            return $tenant;
        });
    }
}
