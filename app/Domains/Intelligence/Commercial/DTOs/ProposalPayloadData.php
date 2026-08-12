<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Commercial\DTOs;

final class ProposalPayloadData
{
    public function __construct(
        public readonly array $packageLines,
        public readonly array $deploymentLines,
        public readonly array $serviceLines,
        public readonly array $assumptions,
        public readonly array $risks,
    ) {}

    public function toArray(): array
    {
        return [
            'package_lines' => $this->packageLines,
            'deployment_lines' => $this->deploymentLines,
            'service_lines' => $this->serviceLines,
            'assumptions' => $this->assumptions,
            'risks' => $this->risks,
        ];
    }
}