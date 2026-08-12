<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Commercial\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TenantSecurityProfile extends CommercialRecord
{
    protected $fillable =         array (
          0 => 'intelligence_tenant_id',
          1 => 'policy_level',
          2 => 'mfa_required',
          3 => 'ip_allow_list',
          4 => 'data_encryption_level',
          5 => 'metadata',
        );

    protected function casts(): array
    {
        return         array (
          'mfa_required' => 'boolean',
          'ip_allow_list' => 'array',
          'metadata' => 'array',
        );
    }


    public function tenant(): BelongsTo
    {
        return $this->belongsTo(IntelligenceTenant::class, 'intelligence_tenant_id');
    }
}