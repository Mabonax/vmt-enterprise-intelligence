<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Commercial\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MaintenanceWindow extends CommercialRecord
{
    protected $fillable =         array (
          0 => 'intelligence_tenant_id',
          1 => 'title',
          2 => 'starts_at',
          3 => 'ends_at',
          4 => 'status',
          5 => 'metadata',
        );

    protected function casts(): array
    {
        return         array (
          'starts_at' => 'datetime',
          'ends_at' => 'datetime',
          'metadata' => 'array',
        );
    }

}