<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Commercial\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class IntelligencePackage extends CommercialRecord
{
    protected $fillable =         array (
          0 => 'name',
          1 => 'slug',
          2 => 'tier',
          3 => 'description',
          4 => 'support_level',
          5 => 'sla_level',
          6 => 'deployment_mode_eligibility',
          7 => 'enabled_agent_capabilities',
          8 => 'monthly_execution_limit',
          9 => 'token_usage_limit',
          10 => 'knowledge_storage_limit_mb',
          11 => 'team_member_limit',
          12 => 'approval_workflow_limit',
          13 => 'connector_limit',
          14 => 'is_active',
          15 => 'metadata',
        );

    protected function casts(): array
    {
        return         array (
          'deployment_mode_eligibility' => 'array',
          'enabled_agent_capabilities' => 'array',
          'is_active' => 'boolean',
          'metadata' => 'array',
        );
    }


    public function features(): HasMany
    {
        return $this->hasMany(PackageFeature::class);
    }

    public function limits(): HasMany
    {
        return $this->hasMany(PackageLimit::class);
    }

    public function entitlements(): HasMany
    {
        return $this->hasMany(PackageEntitlement::class);
    }

    public function moduleAccesses(): HasMany
    {
        return $this->hasMany(PackageModuleAccess::class);
    }

    public function pricingRules(): HasMany
    {
        return $this->hasMany(PackagePricingRule::class);
    }

    public function upgradePaths(): HasMany
    {
        return $this->hasMany(PackageUpgradePath::class, 'from_package_id');
    }
}