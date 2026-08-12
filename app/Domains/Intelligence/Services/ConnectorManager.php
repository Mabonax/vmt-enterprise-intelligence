<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Services;

use App\Domains\Intelligence\Connectors\CacheConnector;
use App\Domains\Intelligence\Connectors\CalendarConnector;
use App\Domains\Intelligence\Connectors\DatabaseConnector;
use App\Domains\Intelligence\Connectors\EmailConnector;
use App\Domains\Intelligence\Connectors\FilesystemConnector;
use App\Domains\Intelligence\Connectors\GraphQLConnector;
use App\Domains\Intelligence\Connectors\LaravelConnector;
use App\Domains\Intelligence\Connectors\MCPConnector;
use App\Domains\Intelligence\Connectors\OpenAPIConnector;
use App\Domains\Intelligence\Connectors\QueueConnector;
use App\Domains\Intelligence\Connectors\RedisConnector;
use App\Domains\Intelligence\Connectors\RESTConnector;
use App\Domains\Intelligence\Connectors\StorageConnector;
use App\Domains\Intelligence\Connectors\WebhookConnector;
use App\Domains\Intelligence\DTOs\ToolContext;
use App\Domains\Intelligence\Tools\Contracts\ConnectorInterface;
use App\Domains\Intelligence\Tools\Exceptions\ConnectorExecutionException;
use App\Domains\Intelligence\Tools\Models\ConnectorHealth;
use App\Domains\Intelligence\Tools\Models\ConnectorRegistration;
use App\Domains\Intelligence\Tools\Models\EnterpriseTool;
use Illuminate\Contracts\Container\Container;

class ConnectorManager
{
    /**
     * @var list<class-string<ConnectorInterface>>
     */
    private array $connectorClasses = [
        LaravelConnector::class,
        DatabaseConnector::class,
        RESTConnector::class,
        GraphQLConnector::class,
        FilesystemConnector::class,
        WebhookConnector::class,
        RedisConnector::class,
        CacheConnector::class,
        QueueConnector::class,
        OpenAPIConnector::class,
        MCPConnector::class,
        EmailConnector::class,
        CalendarConnector::class,
        StorageConnector::class,
    ];

    public function __construct(
        private readonly Container $container,
    ) {}

    public function syncRegistrations(): void
    {
        foreach ($this->all() as $connector) {
            $registration = ConnectorRegistration::query()->updateOrCreate(
                ['slug' => $connector->key()],
                [
                    'name' => $connector->label(),
                    'driver' => $connector::class,
                    'status' => 'active',
                    'configuration' => [],
                    'metadata' => ['discovered' => true],
                ],
            );

            $health = $connector->health();

            ConnectorHealth::query()->updateOrCreate(
                ['connector_registration_id' => $registration->id],
                [
                    'status' => (string) ($health['status'] ?? 'healthy'),
                    'latency_ms' => (int) ($health['latency_ms'] ?? 0),
                    'last_checked_at' => now(),
                    'metadata' => $health,
                ],
            );
        }
    }

    /**
     * @return list<ConnectorInterface>
     */
    public function all(): array
    {
        return array_map(fn (string $class): ConnectorInterface => $this->container->make($class), $this->connectorClasses);
    }

    public function resolve(string $key): ConnectorInterface
    {
        foreach ($this->all() as $connector) {
            if ($connector->key() === $key) {
                return $connector;
            }
        }

        throw new ConnectorExecutionException("Connector [{$key}] is not registered.");
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(EnterpriseTool $tool, array $payload, ToolContext $context): array
    {
        return $this->resolve($tool->connector_type)->execute($tool, $payload, $context);
    }
}
