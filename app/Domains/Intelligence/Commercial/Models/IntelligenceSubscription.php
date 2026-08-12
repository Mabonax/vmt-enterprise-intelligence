<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Commercial\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class IntelligenceSubscription extends CommercialRecord
{
    protected $fillable =         array (
          0 => 'intelligence_tenant_id',
          1 => 'intelligence_package_id',
          2 => 'billing_account_id',
          3 => 'status',
          4 => 'starts_at',
          5 => 'ends_at',
          6 => 'renews_at',
          7 => 'monthly_price',
          8 => 'currency',
          9 => 'metadata',
        );

    protected function casts(): array
    {
        return         array (
          'starts_at' => 'datetime',
          'ends_at' => 'datetime',
          'renews_at' => 'datetime',
          'monthly_price' => 'decimal:2',
          'metadata' => 'array',
        );
    }


    public function tenant(): BelongsTo
    {
        return $this->belongsTo(IntelligenceTenant::class, 'intelligence_tenant_id');
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(IntelligencePackage::class, 'intelligence_package_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(SubscriptionInvoice::class);
    }

    public function meters(): HasMany
    {
        return $this->hasMany(UsageMeter::class);
    }

    public function quotas(): HasMany
    {
        return $this->hasMany(UsageQuota::class);
    }

    public function overages(): HasMany
    {
        return $this->hasMany(UsageOverage::class);
    }

    public function billingAccount(): BelongsTo
    {
        return $this->belongsTo(BillingAccount::class);
    }
}