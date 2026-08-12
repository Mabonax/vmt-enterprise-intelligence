<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Connectors;

use App\Domains\Intelligence\DTOs\ToolContext;
use App\Domains\Intelligence\Tools\Exceptions\ConnectorExecutionException;
use App\Domains\Intelligence\Tools\Models\EnterpriseTool;
use Illuminate\Support\Facades\DB;

class DatabaseConnector extends AbstractConnector
{
    public function key(): string
    {
        return 'database';
    }

    public function label(): string
    {
        return 'Database Connector';
    }

    public function execute(EnterpriseTool $tool, array $payload, ToolContext $context): array
    {
        $query = $tool->metadata['query'] ?? null;

        if (! is_string($query) || $query === '') {
            throw new ConnectorExecutionException("Database connector tool [{$tool->slug}] has no query.");
        }

        return ['rows' => DB::select($query, $payload)];
    }
}
