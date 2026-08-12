<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Commercial\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SubscriptionInvoiceLine extends CommercialRecord
{
    protected $fillable =         array (
          0 => 'subscription_invoice_id',
          1 => 'line_type',
          2 => 'description',
          3 => 'quantity',
          4 => 'unit_price',
          5 => 'line_total',
          6 => 'metadata',
        );

    protected function casts(): array
    {
        return         array (
          'quantity' => 'decimal:2',
          'unit_price' => 'decimal:2',
          'line_total' => 'decimal:2',
          'metadata' => 'array',
        );
    }


    public function invoice(): BelongsTo
    {
        return $this->belongsTo(SubscriptionInvoice::class, 'subscription_invoice_id');
    }
}