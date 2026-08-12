<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Commercial\Services;

use App\Domains\Intelligence\Commercial\Models\IntelligenceTenant;
use App\Domains\Intelligence\Commercial\Models\TenantSecurityProfile;

class TenantSecurityConfigurator
{
    public function configure(IntelligenceTenant $tenant, array $attributes = []): TenantSecurityProfile
    {
        return TenantSecurityProfile::query()->updateOrCreate(
            ['intelligence_tenant_id' => $tenant->id],
            array_merge([
                'policy_level' => 'enterprise',
                'mfa_required' => true,
                'ip_allow_list' => [],
                'data_encryption_level' => 'at_rest_and_transit',
                'metadata' => ['configured_by' => 'phase_nine'],
            ], $attributes),
        );
    }
}