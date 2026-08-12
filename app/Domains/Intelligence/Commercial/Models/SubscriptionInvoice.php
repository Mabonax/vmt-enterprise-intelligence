<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Commercial\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SubscriptionInvoice extends CommercialRecord
{
    protected $fillable =         array (
          0 => 'intelligence_subscription_id',
          1 => 'billing_account_id',
          2 => 'invoice_number',
          3 => 'status',
          4 => 'period_start',
          5 => 'period_end',
          6 => 'subtotal',
          7 => 'tax_total',
          8 => 'grand_total',
          9 => 'currency',
          10 => 'payload',
        );

    protected function casts(): array
    {
        return         array (
          'period_start' => 'datetime',
          'period_end' => 'datetime',
          'subtotal' => 'decimal:2',
          'tax_total' => 'decimal:2',
          'grand_total' => 'decimal:2',
          'payload' => 'array',
        );
    }


    public function subscription(): BelongsTo
    {
        return $this->belongsTo(IntelligenceSubscription::class, 'intelligence_subscription_id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(SubscriptionInvoiceLine::class);
    }
}