<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Commercial\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class UsageMeter extends CommercialRecord
{
    protected $fillable =         array (
          0 => 'intelligence_subscription_id',
          1 => 'meter_key',
          2 => 'label',
          3 => 'usage_total',
          4 => 'usage_limit',
          5 => 'warning_threshold',
          6 => 'hard_limit',
          7 => 'period_started_at',
          8 => 'period_ends_at',
          9 => 'metadata',
        );

    protected function casts(): array
    {
        return         array (
          'usage_total' => 'decimal:2',
          'usage_limit' => 'decimal:2',
          'warning_threshold' => 'decimal:2',
          'hard_limit' => 'boolean',
          'period_started_at' => 'datetime',
          'period_ends_at' => 'datetime',
          'metadata' => 'array',
        );
    }


    public function subscription(): BelongsTo
    {
        return $this->belongsTo(IntelligenceSubscription::class, 'intelligence_subscription_id');
    }

    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(UsageLedgerEntry::class);
    }
}