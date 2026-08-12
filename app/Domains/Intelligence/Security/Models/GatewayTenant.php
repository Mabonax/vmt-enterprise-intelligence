<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Security\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GatewayTenant extends Model
{
    use HasUuids;

    public $incrementing = false;

    protected $guarded = [];

    protected $keyType = 'string';

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'metadata' => 'array',
        ];
    }

    public function clients(): HasMany
    {
        return $this->hasMany(GatewayClient::class);
    }
}
