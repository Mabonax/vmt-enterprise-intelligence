<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Commercial\Services;

use App\Domains\Intelligence\Commercial\Models\IntelligenceTenant;
use App\Domains\Intelligence\Commercial\Models\TenantBrandingProfile;

class TenantBrandingService
{
    public function apply(IntelligenceTenant $tenant, array $attributes = []): TenantBrandingProfile
    {
        return TenantBrandingProfile::query()->updateOrCreate(
            ['intelligence_tenant_id' => $tenant->id],
            array_merge([
                'brand_name' => $tenant->name,
                'primary_color' => '#111111',
                'secondary_color' => '#7c7c7c',
                'metadata' => ['branding_ready' => true],
            ], $attributes),
        );
    }
}