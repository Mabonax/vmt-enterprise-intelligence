<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\DTOs;

final readonly class ExecutionPlanData
{
    /**
     * @param list<ExecutionPlanStepData> $steps
     * @param list<string> $requiredTools
     * @param list<string> $dependencies
     */
    public function __construct(
        public string $objective,
        public array $steps,
        public array $requiredTools = [],
        public array $dependencies = [],
        public string $estimatedComplexity = 'medium',
        public string $completionState = 'open',
        public int $maxIterations = 5,
        public array $retryStrategy = [],
        public array $metadata = [],
    ) {}
}
