<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\DTOs;

final readonly class UsageStatistics
{
    public function __construct(
        public int $inputTokens = 0,
        public int $outputTokens = 0,
        public int $totalTokens = 0,
        public ?int $latencyMs = null,
        public ?float $cost = null,
        public bool $success = true,
        public ?string $error = null,
    ) {}

    /**
     * @return array{input_tokens: int, output_tokens: int, total_tokens: int, latency_ms: int|null, cost: float|null, success: bool, error: string|null}
     */
    public function toArray(): array
    {
        return [
            'input_tokens' => $this->inputTokens,
            'output_tokens' => $this->outputTokens,
            'total_tokens' => $this->totalTokens,
            'latency_ms' => $this->latencyMs,
            'cost' => $this->cost,
            'success' => $this->success,
            'error' => $this->error,
        ];
    }
}
