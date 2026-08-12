<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Gateway\DTOs;

final readonly class GatewaySubjectData
{
    public function __construct(
        public string $type,
        public string $id,
        public array $metadata = [],
    ) {}

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'id' => $this->id,
            'metadata' => $this->metadata,
        ];
    }
}
