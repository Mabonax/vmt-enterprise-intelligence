<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Tools;

use App\Domains\Intelligence\Attributes\ToolDefinition;
use App\Domains\Intelligence\Contracts\IntelligenceTool;

#[ToolDefinition(slug: 'document_lookup_stub', category: 'knowledge', tags: ['safe', 'stub'])]
class DocumentLookupStubTool implements IntelligenceTool
{
    public function name(): string
    {
        return 'Document Lookup Stub';
    }

    public function slug(): string
    {
        return 'document_lookup_stub';
    }

    public function description(): string
    {
        return 'Placeholder for future document retrieval.';
    }

    public function category(): string
    {
        return 'knowledge';
    }

    public function permissions(): array
    {
        return [];
    }

    public function schema(): array
    {
        return ['type' => 'object', 'properties' => ['query' => ['type' => 'string']]];
    }

    public function responseSchema(): array
    {
        return ['type' => 'object'];
    }

    public function execute(array $payload): mixed
    {
        return ['matches' => [], 'stub' => true, 'query' => $payload['query'] ?? null];
    }
}
