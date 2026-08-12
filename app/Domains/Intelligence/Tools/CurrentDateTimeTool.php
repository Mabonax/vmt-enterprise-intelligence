<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Tools;

use App\Domains\Intelligence\Attributes\ToolDefinition;
use App\Domains\Intelligence\Contracts\IntelligenceTool;

#[ToolDefinition(slug: 'current_datetime', category: 'runtime', tags: ['safe', 'utility'])]
class CurrentDateTimeTool implements IntelligenceTool
{
    public function name(): string
    {
        return 'Current DateTime';
    }

    public function slug(): string
    {
        return 'current_datetime';
    }

    public function description(): string
    {
        return 'Returns the current platform datetime.';
    }

    public function category(): string
    {
        return 'runtime';
    }

    public function permissions(): array
    {
        return [];
    }

    public function schema(): array
    {
        return ['type' => 'object', 'properties' => []];
    }

    public function responseSchema(): array
    {
        return ['type' => 'object', 'properties' => ['timestamp' => ['type' => 'string']]];
    }

    public function execute(array $payload): mixed
    {
        return ['timestamp' => now()->toIso8601String()];
    }
}
