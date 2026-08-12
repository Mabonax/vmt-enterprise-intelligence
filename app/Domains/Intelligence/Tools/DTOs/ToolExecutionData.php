<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Tools\DTOs;

final readonly class ToolExecutionData
{
    /**
     * @param array<string, mixed> $input
     * @param array<string, mixed> $output
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public string $toolSlug,
        public bool $success,
        public array $input,
        public array $output,
        public ?string $error,
        public int $durationMs,
        public array $metadata = [],
    ) {}
}
