<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Commercial\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TenantBrandingProfile extends CommercialRecord
{
    protected $fillable =         array (
          0 => 'intelligence_tenant_id',
          1 => 'brand_name',
          2 => 'logo_path',
          3 => 'primary_color',
          4 => 'secondary_color',
          5 => 'metadata',
        );

    protected function casts(): array
    {
        return         array (
          'metadata' => 'array',
        );
    }


    public function tenant(): BelongsTo
    {
        return $this->belongsTo(IntelligenceTenant::class, 'intelligence_tenant_id');
    }
}