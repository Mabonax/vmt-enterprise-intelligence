<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Commercial\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class UsageLedgerEntry extends CommercialRecord
{
    protected $fillable =         array (
          0 => 'usage_meter_id',
          1 => 'entry_type',
          2 => 'quantity',
          3 => 'status',
          4 => 'recorded_at',
          5 => 'context',
        );

    protected function casts(): array
    {
        return         array (
          'quantity' => 'decimal:2',
          'recorded_at' => 'datetime',
          'context' => 'array',
        );
    }


    public function meter(): BelongsTo
    {
        return $this->belongsTo(UsageMeter::class, 'usage_meter_id');
    }
}