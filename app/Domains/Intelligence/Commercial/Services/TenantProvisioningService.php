<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Commercial\Services;

use App\Domains\Intelligence\Commercial\DTOs\TenantProvisioningData;
use App\Domains\Intelligence\Commercial\Models\IntelligencePackage;
use App\Domains\Intelligence\Commercial\Models\IntelligenceTenant;
use App\Domains\Intelligence\Commercial\Models\TenantProvisioningChecklist;
use App\Domains\Intelligence\Commercial\Models\TenantProvisioningRequest;
use App\Models\User;

class TenantProvisioningService
{
    public function __construct(
        private readonly TenantWorkspaceBuilder $workspaceBuilder,
        private readonly TenantSecurityConfigurator $securityConfigurator,
        private readonly TenantBrandingService $brandingService,
        private readonly TenantDeploymentPlanner $deploymentPlanner,
        private readonly TenantReadinessService $readinessService,
    ) {}

    public function submit(User $user, IntelligenceTenant $tenant, IntelligencePackage $package, array $attributes = []): TenantProvisioningRequest
    {
        $request = TenantProvisioningRequest::query()->create([
            'intelligence_tenant_id' => $tenant->id,
            'intelligence_package_id' => $package->id,
            'status' => 'submitted',
            'requested_by_user_id' => $user->id,
            'deployment_mode' => $attributes['deployment_mode'] ?? 'shared_saas',
            'go_live_target_at' => now()->addDays(14),
            'notes' => $attributes['notes'] ?? 'Phase 9 additive provisioning request.',
            'checklist_snapshot' => [],
            'metadata' => $attributes['metadata'] ?? [],
        ]);

        foreach ([
            'review_contract' => 'Review commercial packaging and deployment fit',
            'prepare_workspace' => 'Create tenant workspace and default surfaces',
            'apply_security' => 'Apply security and data handling defaults',
            'plan_deployment' => 'Prepare deployment profile and environment plan',
        ] as $taskKey => $label) {
            TenantProvisioningChecklist::query()->create([
                'tenant_provisioning_request_id' => $request->id,
                'task_key' => $taskKey,
                'label' => $label,
                'status' => 'pending',
                'owner_name' => 'Commercial Operations',
                'metadata' => [],
            ]);
        }

        return $request->fresh('checklist');
    }

    public function approve(TenantProvisioningRequest $request): TenantProvisioningData
    {
        $tenant = $request->tenant;
        $request->forceFill(['status' => 'approved'])->save();
        $tenant->forceFill(['status' => 'provisioning', 'deployment_mode' => $request->deployment_mode])->save();

        $workspace = $this->workspaceBuilder->build($tenant);
        $this->securityConfigurator->configure($tenant);
        $this->brandingService->apply($tenant);
        $this->deploymentPlanner->plan($tenant, (string) $request->deployment_mode);

        TenantProvisioningChecklist::query()
            ->where('tenant_provisioning_request_id', $request->id)
            ->update(['status' => 'completed', 'completed_at' => now()]);

        $tenant->forceFill(['status' => 'active'])->save();
        $request->forceFill(['status' => 'active'])->save();

        return new TenantProvisioningData(
            status: 'active',
            checklist: $request->checklist()->get()->toArray(),
            workspace: $workspace->toArray(),
        );
    }

    public function readiness(IntelligenceTenant $tenant): array
    {
        return $this->readinessService->summary($tenant);
    }
}