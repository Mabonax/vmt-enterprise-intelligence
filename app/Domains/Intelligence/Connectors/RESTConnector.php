<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Connectors;

use App\Domains\Intelligence\DTOs\ToolContext;
use App\Domains\Intelligence\Tools\Exceptions\ConnectorExecutionException;
use App\Domains\Intelligence\Tools\Models\EnterpriseTool;
use Illuminate\Support\Facades\Http;

class RESTConnector extends AbstractConnector
{
    public function key(): string
    {
        return 'rest';
    }

    public function label(): string
    {
        return 'REST Connector';
    }

    public function execute(EnterpriseTool $tool, array $payload, ToolContext $context): array
    {
        $endpoint = $tool->metadata['endpoint'] ?? null;
        $method = strtolower((string) ($tool->metadata['method'] ?? 'get'));

        if (! is_string($endpoint) || $endpoint === '') {
            throw new ConnectorExecutionException("REST connector tool [{$tool->slug}] has no endpoint.");
        }

        $response = Http::timeout((int) ($tool->security_policy['timeout_seconds'] ?? 10))
            ->send(strtoupper($method), $endpoint, ['json' => $payload]);

        return [
            'status' => $response->status(),
            'body' => $response->json() ?? ['raw' => $response->body()],
        ];
    }
}
