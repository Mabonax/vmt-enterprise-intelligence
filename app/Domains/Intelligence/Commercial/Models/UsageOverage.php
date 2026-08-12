<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Commercial\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class UsageOverage extends CommercialRecord
{
    protected $fillable =         array (
          0 => 'intelligence_subscription_id',
          1 => 'usage_meter_id',
          2 => 'overage_key',
          3 => 'quantity',
          4 => 'status',
          5 => 'metadata',
        );

    protected function casts(): array
    {
        return         array (
          'quantity' => 'decimal:2',
          'metadata' => 'array',
        );
    }


    public function subscription(): BelongsTo
    {
        return $this->belongsTo(IntelligenceSubscription::class, 'intelligence_subscription_id');
    }
}