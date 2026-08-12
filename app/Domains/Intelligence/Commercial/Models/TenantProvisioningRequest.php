<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Commercial\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TenantProvisioningRequest extends CommercialRecord
{
    protected $fillable =         array (
          0 => 'intelligence_tenant_id',
          1 => 'intelligence_package_id',
          2 => 'status',
          3 => 'requested_by_user_id',
          4 => 'deployment_mode',
          5 => 'go_live_target_at',
          6 => 'notes',
          7 => 'checklist_snapshot',
          8 => 'metadata',
        );

    protected function casts(): array
    {
        return         array (
          'go_live_target_at' => 'datetime',
          'checklist_snapshot' => 'array',
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

    public function checklist(): HasMany
    {
        return $this->hasMany(TenantProvisioningChecklist::class);
    }
}