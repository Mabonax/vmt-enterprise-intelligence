<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Commercial\DTOs;

final class TenantProvisioningData
{
    public function __construct(
        public readonly string $status,
        public readonly array $checklist,
        public readonly array $workspace,
    ) {}

    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'checklist' => $this->checklist,
            'workspace' => $this->workspace,
        ];
    }
}