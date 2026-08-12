<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\DTOs;

final readonly class ExecutionPlanStepData
{
    /**
     * @param list<string> $dependencies
     */
    public function __construct(
        public int $sequence,
        public string $title,
        public ?string $toolSlug = null,
        public string $stepKind = 'task',
        public array $dependencies = [],
        public array $requiredTools = [],
        public array $verificationRequirements = [],
        public int $retryLimit = 1,
        public array $inputPayload = [],
        public array $metadata = [],
    ) {}

    public function toArray(): array
    {
        return [
            'sequence' => $this->sequence,
            'title' => $this->title,
            'tool_slug' => $this->toolSlug,
            'step_kind' => $this->stepKind,
            'dependencies' => $this->dependencies,
            'required_tools' => $this->requiredTools,
            'verification_requirements' => $this->verificationRequirements,
            'retry_limit' => $this->retryLimit,
            'input_payload' => $this->inputPayload,
            'metadata' => $this->metadata,
        ];
    }
}
