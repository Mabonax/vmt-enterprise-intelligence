<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\DTOs;

final readonly class ToolExecutionResult
{
    public function __construct(
        public string $tool,
        public bool $success,
        public array $input = [],
        public array $output = [],
        public ?string $error = null,
        public int $durationMs = 0,
        public array $metadata = [],
    ) {}
}
