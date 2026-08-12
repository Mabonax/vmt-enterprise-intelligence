<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Connectors;

use App\Domains\Intelligence\DTOs\ToolContext;
use App\Domains\Intelligence\Tools\Exceptions\ConnectorExecutionException;
use App\Domains\Intelligence\Tools\Models\EnterpriseTool;
use Illuminate\Support\Facades\Storage;

class FilesystemConnector extends AbstractConnector
{
    public function key(): string
    {
        return 'filesystem';
    }

    public function label(): string
    {
        return 'Filesystem Connector';
    }

    public function execute(EnterpriseTool $tool, array $payload, ToolContext $context): array
    {
        $disk = (string) ($tool->metadata['disk'] ?? 'local');
        $path = $payload['path'] ?? $tool->metadata['path'] ?? null;

        if (! is_string($path) || $path === '') {
            throw new ConnectorExecutionException("Filesystem connector tool [{$tool->slug}] requires a path.");
        }

        return [
            'exists' => Storage::disk($disk)->exists($path),
            'path' => $path,
            'disk' => $disk,
        ];
    }
}
