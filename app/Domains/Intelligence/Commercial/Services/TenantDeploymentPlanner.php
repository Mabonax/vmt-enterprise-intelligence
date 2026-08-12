<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Commercial\Services;

use App\Domains\Intelligence\Commercial\Models\IntelligenceTenant;
use App\Domains\Intelligence\Commercial\Models\TenantDeploymentProfile;
use App\Domains\Intelligence\Commercial\Models\TenantEnvironment;

class TenantDeploymentPlanner
{
    public function plan(IntelligenceTenant $tenant, string $deploymentMode): TenantDeploymentProfile
    {
        $profile = TenantDeploymentProfile::query()->updateOrCreate(
            ['intelligence_tenant_id' => $tenant->id],
            [
                'deployment_mode' => $deploymentMode,
                'environment_strategy' => $deploymentMode === 'shared_saas' ? 'shared_cluster' : 'dedicated_stack',
                'release_channel' => 'stable',
                'metadata' => ['planned_by' => 'phase_nine'],
            ],
        );

        TenantEnvironment::query()->updateOrCreate(
            [
                'intelligence_tenant_id' => $tenant->id,
                'environment_name' => 'production',
            ],
            [
                'environment_type' => 'production',
                'status' => 'ready',
                'endpoint' => 'https://'.$tenant->slug.'.intelligence.local',
                'metadata' => ['deployment_mode' => $deploymentMode],
            ],
        );

        return $profile;
    }
}