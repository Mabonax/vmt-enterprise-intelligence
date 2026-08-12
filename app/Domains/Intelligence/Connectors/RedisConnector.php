<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Connectors;

use App\Domains\Intelligence\DTOs\ToolContext;
use App\Domains\Intelligence\Tools\Models\EnterpriseTool;
use Illuminate\Support\Facades\Redis;

class RedisConnector extends AbstractConnector
{
    public function key(): string
    {
        return 'redis';
    }

    public function label(): string
    {
        return 'Redis Connector';
    }

    public function execute(EnterpriseTool $tool, array $payload, ToolContext $context): array
    {
        $key = (string) ($payload['key'] ?? $tool->metadata['key'] ?? 'intelligence:tool');

        return ['value' => Redis::get($key), 'key' => $key];
    }
}
