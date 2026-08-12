<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Commercial\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PackageUpgradePath extends CommercialRecord
{
    protected $fillable =         array (
          0 => 'from_package_id',
          1 => 'to_package_id',
          2 => 'path_label',
          3 => 'requirements',
          4 => 'is_active',
          5 => 'metadata',
        );

    protected function casts(): array
    {
        return         array (
          'requirements' => 'array',
          'is_active' => 'boolean',
          'metadata' => 'array',
        );
    }


    public function fromPackage(): BelongsTo
    {
        return $this->belongsTo(IntelligencePackage::class, 'from_package_id');
    }

    public function toPackage(): BelongsTo
    {
        return $this->belongsTo(IntelligencePackage::class, 'to_package_id');
    }
}