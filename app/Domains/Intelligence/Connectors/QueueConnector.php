<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Connectors;

use App\Domains\Intelligence\DTOs\ToolContext;
use App\Domains\Intelligence\Tools\Exceptions\ConnectorExecutionException;
use App\Domains\Intelligence\Tools\Models\EnterpriseTool;
use Illuminate\Support\Facades\Queue;

class QueueConnector extends AbstractConnector
{
    public function key(): string
    {
        return 'queue';
    }

    public function label(): string
    {
        return 'Queue Connector';
    }

    public function execute(EnterpriseTool $tool, array $payload, ToolContext $context): array
    {
        $jobClass = $tool->metadata['job'] ?? null;

        if (! is_string($jobClass) || ! class_exists($jobClass)) {
            throw new ConnectorExecutionException("Queue connector tool [{$tool->slug}] has no valid job.");
        }

        Queue::push(new $jobClass($payload));

        return ['queued' => true, 'job' => $jobClass];
    }
}
