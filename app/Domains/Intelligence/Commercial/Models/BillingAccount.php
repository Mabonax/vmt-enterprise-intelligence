<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Commercial\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BillingAccount extends CommercialRecord
{
    protected $fillable =         array (
          0 => 'intelligence_tenant_id',
          1 => 'account_name',
          2 => 'billing_email',
          3 => 'currency',
          4 => 'status',
          5 => 'address_payload',
          6 => 'metadata',
        );

    protected function casts(): array
    {
        return         array (
          'address_payload' => 'array',
          'metadata' => 'array',
        );
    }


    public function tenant(): BelongsTo
    {
        return $this->belongsTo(IntelligenceTenant::class, 'intelligence_tenant_id');
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(BillingContact::class);
    }

    public function paymentInstructions(): HasMany
    {
        return $this->hasMany(PaymentInstruction::class);
    }
}