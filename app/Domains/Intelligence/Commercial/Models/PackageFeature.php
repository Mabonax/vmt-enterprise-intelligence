<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Commercial\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PackageFeature extends CommercialRecord
{
    protected $fillable =         array (
          0 => 'intelligence_package_id',
          1 => 'feature_key',
          2 => 'name',
          3 => 'description',
          4 => 'is_enabled',
          5 => 'metadata',
        );

    protected function casts(): array
    {
        return         array (
          'is_enabled' => 'boolean',
          'metadata' => 'array',
        );
    }


    public function package(): BelongsTo
    {
        return $this->belongsTo(IntelligencePackage::class, 'intelligence_package_id');
    }
}