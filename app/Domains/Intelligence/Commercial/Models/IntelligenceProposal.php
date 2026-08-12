<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Commercial\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class IntelligenceProposal extends CommercialRecord
{
    protected $fillable =         array (
          0 => 'intelligence_tenant_id',
          1 => 'intelligence_package_id',
          2 => 'stage',
          3 => 'title',
          4 => 'summary',
          5 => 'currency',
          6 => 'estimated_monthly_value',
          7 => 'payload',
          8 => 'sent_at',
          9 => 'accepted_at',
          10 => 'metadata',
        );

    protected function casts(): array
    {
        return         array (
          'estimated_monthly_value' => 'decimal:2',
          'payload' => 'array',
          'sent_at' => 'datetime',
          'accepted_at' => 'datetime',
          'metadata' => 'array',
        );
    }


    public function tenant(): BelongsTo
    {
        return $this->belongsTo(IntelligenceTenant::class, 'intelligence_tenant_id');
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(IntelligencePackage::class, 'intelligence_package_id');
    }

    public function packageLines(): HasMany
    {
        return $this->hasMany(ProposalPackageLine::class);
    }

    public function deploymentLines(): HasMany
    {
        return $this->hasMany(ProposalDeploymentLine::class);
    }

    public function serviceLines(): HasMany
    {
        return $this->hasMany(ProposalServiceLine::class);
    }

    public function assumptions(): HasMany
    {
        return $this->hasMany(ProposalAssumption::class);
    }

    public function risks(): HasMany
    {
        return $this->hasMany(ProposalRisk::class);
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(ProposalApproval::class);
    }
}