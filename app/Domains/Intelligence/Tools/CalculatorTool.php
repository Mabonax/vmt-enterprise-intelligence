<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Tools;

use App\Domains\Intelligence\Attributes\ToolDefinition;
use App\Domains\Intelligence\Contracts\IntelligenceTool;
use App\Domains\Intelligence\DTOs\ToolResult;

#[ToolDefinition(slug: 'calculator', category: 'runtime', tags: ['safe', 'utility'])]
class CalculatorTool implements IntelligenceTool
{
    public function name(): string
    {
        return 'Calculator';
    }

    public function slug(): string
    {
        return 'calculator';
    }

    public function description(): string
    {
        return 'Adds two numeric values.';
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
        return ['type' => 'object', 'required' => ['a', 'b']];
    }

    public function responseSchema(): array
    {
        return ['type' => 'object', 'properties' => ['result' => ['type' => 'number']]];
    }

    public function execute(array $payload): mixed
    {
        return new ToolResult(
            success: true,
            payload: ['result' => ((float) ($payload['a'] ?? 0)) + ((float) ($payload['b'] ?? 0))],
        );
    }
}
