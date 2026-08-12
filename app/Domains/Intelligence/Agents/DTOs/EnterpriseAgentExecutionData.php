<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Agents\DTOs;

final readonly class EnterpriseAgentExecutionData
{
    public function __construct(
        public string $objective,
        public string $title,
        public string $executionMode = 'queued',
        public ?string $approvalRole = null,
        public array $context = [],
        public array $metadata = [],
    ) {}
}
