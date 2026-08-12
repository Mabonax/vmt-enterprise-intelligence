<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Commercial\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PaymentInstruction extends CommercialRecord
{
    protected $fillable =         array (
          0 => 'billing_account_id',
          1 => 'instruction_type',
          2 => 'reference',
          3 => 'status',
          4 => 'metadata',
        );

    protected function casts(): array
    {
        return         array (
          'metadata' => 'array',
        );
    }


    public function billingAccount(): BelongsTo
    {
        return $this->belongsTo(BillingAccount::class);
    }
}