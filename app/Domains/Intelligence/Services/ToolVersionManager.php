<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Services;

use App\Domains\Intelligence\Tools\DTOs\ToolManifestData;
use App\Domains\Intelligence\Tools\Models\EnterpriseTool;
use App\Domains\Intelligence\Tools\Models\ToolVersion;

class ToolVersionManager
{
    public function sync(EnterpriseTool $tool, ToolManifestData $manifest): void
    {
        ToolVersion::query()->where('enterprise_tool_id', $tool->id)->update(['is_current' => false]);

        ToolVersion::query()->updateOrCreate(
            [
                'enterprise_tool_id' => $tool->id,
                'version' => $manifest->version,
            ],
            [
                'is_current' => true,
                'compatibility_range' => '^'.$manifest->version,
                'manifest_payload' => [
                    'inputs' => $manifest->inputs,
                    'outputs' => $manifest->outputs,
                    'permissions' => $manifest->permissions,
                ],
                'metadata' => $manifest->metadata,
            ],
        );
    }

    public function resolve(EnterpriseTool $tool, ?string $version = null): EnterpriseTool
    {
        if ($version === null || $tool->version === $version) {
            return $tool;
        }

        $resolved = EnterpriseTool::query()
            ->where('slug', $tool->slug)
            ->where('version', $version)
            ->first();

        return $resolved ?? $tool;
    }
}
