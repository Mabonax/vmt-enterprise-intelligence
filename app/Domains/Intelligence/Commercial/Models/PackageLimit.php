<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Commercial\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PackageLimit extends CommercialRecord
{
    protected $fillable =         array (
          0 => 'intelligence_package_id',
          1 => 'limit_key',
          2 => 'limit_value',
          3 => 'unit',
          4 => 'is_hard_limit',
          5 => 'metadata',
        );

    protected function casts(): array
    {
        return         array (
          'limit_value' => 'decimal:2',
          'is_hard_limit' => 'boolean',
          'metadata' => 'array',
        );
    }


    public function package(): BelongsTo
    {
        return $this->belongsTo(IntelligencePackage::class, 'intelligence_package_id');
    }
}