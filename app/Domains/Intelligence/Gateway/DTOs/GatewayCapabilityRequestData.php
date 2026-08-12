<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Gateway\DTOs;

final readonly class GatewayCapabilityRequestData
{
    /**
     * @param list<string> $knowledgeReferences
     * @param list<array{tool: string, payload?: array<string, mixed>}> $actions
     * @param array<string, mixed> $context
     * @param array<string, mixed> $options
     */
    public function __construct(
        public string $capability,
        public string $organizationId,
        public string $erpSystem,
        public string $correlationId,
        public GatewayActorData $actor,
        public ?GatewaySubjectData $subject,
        public string $prompt,
        public array $context = [],
        public array $knowledgeReferences = [],
        public array $actions = [],
        public array $options = [],
    ) {}

    public function allowActions(): bool
    {
        return (bool) ($this->options['allow_actions'] ?? false) || $this->capability === 'action';
    }
}
