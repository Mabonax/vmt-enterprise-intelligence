<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Security\DTOs;

final readonly class GatewayApiKeyMaterialData
{
    public function __construct(
        public string $keyIdentifier,
        public string $apiKey,
        public string $apiSecret,
        public int $version,
        public ?string $expiresAt = null,
    ) {}
}
