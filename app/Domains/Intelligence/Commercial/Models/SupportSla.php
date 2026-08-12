<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Commercial\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SupportSla extends CommercialRecord
{
    protected $fillable =         array (
          0 => 'support_plan_id',
          1 => 'name',
          2 => 'response_time_minutes',
          3 => 'resolution_time_minutes',
          4 => 'metadata',
        );

    protected function casts(): array
    {
        return         array (
          'metadata' => 'array',
        );
    }

}