<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Commercial\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class IntelligenceTenant extends CommercialRecord
{
    protected $fillable =         array (
          0 => 'name',
          1 => 'slug',
          2 => 'industry',
          3 => 'status',
          4 => 'deployment_mode',
          5 => 'primary_contact_name',
          6 => 'primary_contact_email',
          7 => 'package_snapshot',
          8 => 'metadata',
        );

    protected function casts(): array
    {
        return         array (
          'package_snapshot' => 'array',
          'metadata' => 'array',
        );
    }


    public function workspaces(): HasMany
    {
        return $this->hasMany(TenantWorkspace::class);
    }

    public function provisioningRequests(): HasMany
    {
        return $this->hasMany(TenantProvisioningRequest::class);
    }

    public function domains(): HasMany
    {
        return $this->hasMany(TenantDomain::class);
    }

    public function environments(): HasMany
    {
        return $this->hasMany(TenantEnvironment::class);
    }

    public function brandingProfile(): HasOne
    {
        return $this->hasOne(TenantBrandingProfile::class);
    }

    public function securityProfile(): HasOne
    {
        return $this->hasOne(TenantSecurityProfile::class);
    }

    public function dataResidencyProfile(): HasOne
    {
        return $this->hasOne(TenantDataResidencyProfile::class);
    }

    public function deploymentProfile(): HasOne
    {
        return $this->hasOne(TenantDeploymentProfile::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(IntelligenceSubscription::class);
    }

    public function intelligenceProposals(): HasMany
    {
        return $this->hasMany(IntelligenceProposal::class);
    }

    public function deploymentRunbooks(): HasMany
    {
        return $this->hasMany(DeploymentRunbook::class);
    }

    public function supportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class);
    }

    public function maintenanceWindows(): HasMany
    {
        return $this->hasMany(MaintenanceWindow::class);
    }

    public function releaseReadinessChecks(): HasMany
    {
        return $this->hasMany(ReleaseReadinessCheck::class);
    }
}
