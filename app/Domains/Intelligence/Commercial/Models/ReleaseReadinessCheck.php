<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Commercial\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ReleaseReadinessCheck extends CommercialRecord
{
    protected $fillable =         array (
          0 => 'intelligence_tenant_id',
          1 => 'deployment_runbook_id',
          2 => 'status',
          3 => 'score',
          4 => 'check_payload',
          5 => 'checked_at',
          6 => 'metadata',
        );

    protected function casts(): array
    {
        return         array (
          'score' => 'decimal:2',
          'check_payload' => 'array',
          'checked_at' => 'datetime',
          'metadata' => 'array',
        );
    }

}