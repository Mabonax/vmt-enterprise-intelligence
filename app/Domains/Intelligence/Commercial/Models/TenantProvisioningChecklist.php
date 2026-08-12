<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Commercial\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TenantProvisioningChecklist extends CommercialRecord
{
    protected $fillable =         array (
          0 => 'tenant_provisioning_request_id',
          1 => 'task_key',
          2 => 'label',
          3 => 'status',
          4 => 'owner_name',
          5 => 'completed_at',
          6 => 'metadata',
        );

    protected function casts(): array
    {
        return         array (
          'completed_at' => 'datetime',
          'metadata' => 'array',
        );
    }


    public function request(): BelongsTo
    {
        return $this->belongsTo(TenantProvisioningRequest::class, 'tenant_provisioning_request_id');
    }
}