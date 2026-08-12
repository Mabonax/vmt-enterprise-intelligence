<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\DTOs;

final readonly class CompletionResult
{
    public function __construct(
        public string $content,
        public UsageStatistics $usage,
        public array $metadata = [],
    ) {}
}
