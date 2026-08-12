<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Commercial\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CommercialHandover extends CommercialRecord
{
    protected $fillable =         array (
          0 => 'intelligence_proposal_id',
          1 => 'intelligence_tenant_id',
          2 => 'handover_status',
          3 => 'handover_payload',
          4 => 'completed_at',
          5 => 'metadata',
        );

    protected function casts(): array
    {
        return         array (
          'handover_payload' => 'array',
          'completed_at' => 'datetime',
          'metadata' => 'array',
        );
    }

}