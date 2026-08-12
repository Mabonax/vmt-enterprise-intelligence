<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Commercial\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SupportPlan extends CommercialRecord
{
    protected $fillable =         array (
          0 => 'intelligence_tenant_id',
          1 => 'intelligence_package_id',
          2 => 'support_level',
          3 => 'sla_name',
          4 => 'coverage_hours',
          5 => 'metadata',
        );

    protected function casts(): array
    {
        return         array (
          'metadata' => 'array',
        );
    }

}