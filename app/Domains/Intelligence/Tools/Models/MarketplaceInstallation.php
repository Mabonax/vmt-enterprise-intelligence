<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Tools\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class MarketplaceInstallation extends Model
{
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['marketplace_package_id', 'enterprise_tool_id', 'status', 'installed_version', 'metadata'];

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }
}
