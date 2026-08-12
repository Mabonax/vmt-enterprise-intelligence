<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Services;

use App\Domains\Intelligence\Tools\Models\EnterpriseTool;
use App\Domains\Intelligence\Tools\Models\MarketplacePackage;
use App\Domains\Intelligence\Tools\Models\ToolPackage;

class ToolPublisher
{
    public function publish(EnterpriseTool $tool): MarketplacePackage
    {
        $package = ToolPackage::query()->updateOrCreate(
            ['slug' => $tool->slug],
            [
                'name' => $tool->name,
                'version' => $tool->version,
                'publisher' => $tool->publisher ?? 'VMT',
                'status' => 'published',
                'metadata' => $tool->metadata,
            ],
        );

        return MarketplacePackage::query()->updateOrCreate(
            ['slug' => $tool->slug],
            [
                'tool_package_id' => $package->id,
                'name' => $tool->name,
                'status' => 'available',
                'version' => $tool->version,
                'metadata' => ['connector' => $tool->connector_type],
            ],
        );
    }
}
