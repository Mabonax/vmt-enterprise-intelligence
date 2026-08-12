<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Commercial\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BillingContact extends CommercialRecord
{
    protected $fillable =         array (
          0 => 'billing_account_id',
          1 => 'name',
          2 => 'email',
          3 => 'phone',
          4 => 'role',
          5 => 'is_primary',
          6 => 'metadata',
        );

    protected function casts(): array
    {
        return         array (
          'is_primary' => 'boolean',
          'metadata' => 'array',
        );
    }


    public function billingAccount(): BelongsTo
    {
        return $this->belongsTo(BillingAccount::class);
    }
}