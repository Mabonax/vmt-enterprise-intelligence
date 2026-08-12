<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Gateway\DTOs;

final readonly class GatewayActorData
{
    /**
     * @param list<string> $roles
     * @param list<string> $permissions
     */
    public function __construct(
        public string $id,
        public array $roles = [],
        public array $permissions = [],
        public array $metadata = [],
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'roles' => $this->roles,
            'permissions' => $this->permissions,
            'metadata' => $this->metadata,
        ];
    }
}
