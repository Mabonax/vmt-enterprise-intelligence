<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Tools;

use App\Domains\Intelligence\Attributes\ToolDefinition;
use App\Domains\Intelligence\Contracts\IntelligenceTool;

#[ToolDefinition(slug: 'erp_lookup_stub', category: 'erp', tags: ['safe', 'stub'])]
class ErpLookupStubTool implements IntelligenceTool
{
    public function name(): string
    {
        return 'ERP Lookup Stub';
    }

    public function slug(): string
    {
        return 'erp_lookup_stub';
    }

    public function description(): string
    {
        return 'Placeholder for future ERP domain tool lookups.';
    }

    public function category(): string
    {
        return 'erp';
    }

    public function permissions(): array
    {
        return [];
    }

    public function schema(): array
    {
        return ['type' => 'object', 'properties' => ['domain' => ['type' => 'string']]];
    }

    public function responseSchema(): array
    {
        return ['type' => 'object'];
    }

    public function execute(array $payload): mixed
    {
        return ['records' => [], 'stub' => true, 'domain' => $payload['domain'] ?? null];
    }
}
