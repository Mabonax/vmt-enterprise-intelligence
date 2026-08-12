<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Commercial\Services;

use App\Domains\Intelligence\Commercial\Models\IntelligenceTenant;
use App\Domains\Intelligence\Commercial\Models\TenantWorkspace;

class TenantWorkspaceBuilder
{
    public function build(IntelligenceTenant $tenant): TenantWorkspace
    {
        return TenantWorkspace::query()->updateOrCreate(
            [
                'intelligence_tenant_id' => $tenant->id,
                'workspace_key' => $tenant->slug.'-workspace',
            ],
            [
                'name' => $tenant->name.' Workspace',
                'status' => $tenant->status === 'active' ? 'active' : 'provisioning',
                'metadata' => ['generated_by' => 'phase_nine'],
            ],
        );
    }
}