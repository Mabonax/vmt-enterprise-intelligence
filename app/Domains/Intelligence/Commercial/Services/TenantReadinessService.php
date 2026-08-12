<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Commercial\Services;

use App\Domains\Intelligence\Commercial\Models\IntelligenceTenant;

class TenantReadinessService
{
    public function summary(IntelligenceTenant $tenant): array
    {
        return [
            'workspace_ready' => $tenant->workspaces()->exists(),
            'security_ready' => $tenant->securityProfile()->exists(),
            'branding_ready' => $tenant->brandingProfile()->exists(),
            'deployment_ready' => $tenant->deploymentProfile()->exists(),
            'domain_count' => $tenant->domains()->count(),
        ];
    }
}