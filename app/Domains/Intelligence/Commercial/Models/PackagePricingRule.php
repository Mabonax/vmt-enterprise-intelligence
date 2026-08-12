<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Commercial\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PackagePricingRule extends CommercialRecord
{
    protected $fillable =         array (
          0 => 'intelligence_package_id',
          1 => 'billing_frequency',
          2 => 'currency',
          3 => 'base_price',
          4 => 'overage_policy',
          5 => 'metadata',
        );

    protected function casts(): array
    {
        return         array (
          'base_price' => 'decimal:2',
          'metadata' => 'array',
        );
    }


    public function package(): BelongsTo
    {
        return $this->belongsTo(IntelligencePackage::class, 'intelligence_package_id');
    }
}