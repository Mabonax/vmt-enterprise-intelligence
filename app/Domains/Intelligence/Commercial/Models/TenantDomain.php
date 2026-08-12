<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Commercial\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TenantDomain extends CommercialRecord
{
    protected $fillable =         array (
          0 => 'intelligence_tenant_id',
          1 => 'domain',
          2 => 'domain_type',
          3 => 'is_primary',
          4 => 'verified_at',
          5 => 'metadata',
        );

    protected function casts(): array
    {
        return         array (
          'is_primary' => 'boolean',
          'verified_at' => 'datetime',
          'metadata' => 'array',
        );
    }


    public function tenant(): BelongsTo
    {
        return $this->belongsTo(IntelligenceTenant::class, 'intelligence_tenant_id');
    }
}