<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Connectors;

use App\Domains\Intelligence\DTOs\ToolContext;
use App\Domains\Intelligence\Tools\Exceptions\ConnectorExecutionException;
use App\Domains\Intelligence\Tools\Models\EnterpriseTool;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Facades\DB;

class LaravelConnector extends AbstractConnector
{
    public function __construct(
        private readonly Container $container,
    ) {}

    public function key(): string
    {
        return 'laravel';
    }

    public function label(): string
    {
        return 'Laravel Domain Connector';
    }

    public function execute(EnterpriseTool $tool, array $payload, ToolContext $context): array
    {
        $target = $tool->metadata['target'] ?? null;

        if (! is_array($target) || ! isset($target['service'], $target['method'])) {
            if ($tool->handler_class !== null && class_exists($tool->handler_class)) {
                $handler = $this->container->make($tool->handler_class);

                if (method_exists($handler, 'execute')) {
                    $result = $handler->execute(array_merge($payload, ['_tool_context' => $context]));

                    return is_array($result) ? $result : ['result' => $result];
                }
            }

            throw new ConnectorExecutionException("Laravel connector tool [{$tool->slug}] has no valid target.");
        }

        $service = $this->container->make((string) $target['service']);
        $method = (string) $target['method'];

        if (! method_exists($service, $method)) {
            throw new ConnectorExecutionException("Service method [{$method}] does not exist on [{$target['service']}].");
        }

        $callback = fn (): mixed => $service->{$method}($payload, $context);
        $transactional = (bool) ($tool->security_policy['transactional'] ?? false);
        $result = $transactional ? DB::transaction($callback) : $callback();

        return is_array($result) ? $result : ['result' => $result];
    }
}
