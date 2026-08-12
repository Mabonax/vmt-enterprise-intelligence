<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Commercial\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class UsageQuota extends CommercialRecord
{
    protected $fillable =         array (
          0 => 'intelligence_subscription_id',
          1 => 'quota_key',
          2 => 'quota_limit',
          3 => 'consumed',
          4 => 'warning_state',
          5 => 'enforcement_mode',
          6 => 'metadata',
        );

    protected function casts(): array
    {
        return         array (
          'quota_limit' => 'decimal:2',
          'consumed' => 'decimal:2',
          'warning_state' => 'boolean',
          'metadata' => 'array',
        );
    }


    public function subscription(): BelongsTo
    {
        return $this->belongsTo(IntelligenceSubscription::class, 'intelligence_subscription_id');
    }
}