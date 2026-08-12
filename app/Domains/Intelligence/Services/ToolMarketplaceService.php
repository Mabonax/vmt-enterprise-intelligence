<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Services;

use App\Domains\Intelligence\Tools\Models\MarketplacePackage;

class ToolMarketplaceService
{
    public function availablePackages(): array
    {
        return MarketplacePackage::query()->orderBy('name')->get()->toArray();
    }
}
