<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\DTOs;

final readonly class ContextWindow
{
    public function __construct(
        public int $maxTokens,
        public int $reservedOutputTokens,
        public int $usedTokens = 0,
    ) {}

    public function availableTokens(): int
    {
        return max(0, $this->maxTokens - $this->reservedOutputTokens - $this->usedTokens);
    }
}
