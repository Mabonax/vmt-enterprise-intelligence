<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Tools;

use App\Domains\Intelligence\Attributes\ToolDefinition;
use App\Domains\Intelligence\Contracts\IntelligenceTool;

#[ToolDefinition(slug: 'platform_status', category: 'platform', tags: ['safe', 'diagnostic'])]
class PlatformStatusTool implements IntelligenceTool
{
    public function name(): string
    {
        return 'Platform Status';
    }

    public function slug(): string
    {
        return 'platform_status';
    }

    public function description(): string
    {
        return 'Returns runtime configuration status information.';
    }

    public function category(): string
    {
        return 'platform';
    }

    public function permissions(): array
    {
        return [];
    }

    public function schema(): array
    {
        return ['type' => 'object'];
    }

    public function responseSchema(): array
    {
        return ['type' => 'object'];
    }

    public function execute(array $payload): mixed
    {
        return [
            'default_provider' => config('intelligence.default_provider'),
            'default_model' => config('intelligence.default_model'),
            'streaming_enabled' => config('intelligence.streaming.enabled'),
            'tool_execution_enabled' => config('intelligence.tool_runtime.enabled'),
        ];
    }
}
