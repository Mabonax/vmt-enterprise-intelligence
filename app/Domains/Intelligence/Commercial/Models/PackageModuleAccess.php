<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Commercial\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PackageModuleAccess extends CommercialRecord
{
    protected $fillable =         array (
          0 => 'intelligence_package_id',
          1 => 'module_key',
          2 => 'access_level',
          3 => 'metadata',
        );

    protected function casts(): array
    {
        return         array (
          'metadata' => 'array',
        );
    }


    public function package(): BelongsTo
    {
        return $this->belongsTo(IntelligencePackage::class, 'intelligence_package_id');
    }
}