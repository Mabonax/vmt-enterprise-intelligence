<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Services;

use App\Domains\Intelligence\Tools\Models\EnterpriseTool;
use App\Domains\Intelligence\Tools\Models\MarketplaceInstallation;
use App\Domains\Intelligence\Tools\Models\MarketplacePackage;

class ToolInstaller
{
    public function install(MarketplacePackage $package, EnterpriseTool $tool): MarketplaceInstallation
    {
        return MarketplaceInstallation::query()->updateOrCreate(
            [
                'marketplace_package_id' => $package->id,
                'enterprise_tool_id' => $tool->id,
            ],
            [
                'status' => 'installed',
                'installed_version' => $tool->version,
                'metadata' => ['installed_at' => now()->toIso8601String()],
            ],
        );
    }
}
