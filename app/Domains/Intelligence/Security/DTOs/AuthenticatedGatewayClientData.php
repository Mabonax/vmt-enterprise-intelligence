<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Security\DTOs;

use App\Domains\Connections\Models\ConnectedErp;
use App\Domains\Intelligence\Security\Enums\GatewayAuthMethod;
use App\Domains\Intelligence\Security\Models\GatewayApiCredential;
use App\Domains\Intelligence\Security\Models\GatewayClient;
use App\Domains\Intelligence\Security\Models\GatewayTenant;

final readonly class AuthenticatedGatewayClientData
{
    /**
     * @param list<string> $scopes
     * @param list<string> $capabilities
     * @param list<string> $providers
     * @param list<string> $models
     */
    public function __construct(
        public GatewayAuthMethod $authMethod,
        public ?GatewayTenant $tenant,
        public ?GatewayClient $client,
        public ?GatewayApiCredential $credential,
        public ?ConnectedErp $legacyErp,
        public string $organizationId,
        public string $clientName,
        public string $clientKey,
        public array $scopes = [],
        public array $capabilities = [],
        public array $providers = [],
        public array $models = [],
    ) {}

    public function tenantId(): ?string
    {
        return $this->tenant?->getKey();
    }

    public function clientId(): ?string
    {
        return $this->client?->getKey();
    }

    public function isLegacy(): bool
    {
        return $this->legacyErp !== null;
    }
}
