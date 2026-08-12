<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Services;

use App\Domains\Intelligence\Contracts\TokenCounter;
use App\Domains\Intelligence\DTOs\ChatMessage;
use App\Domains\Intelligence\DTOs\ContextWindow;
use App\Domains\Intelligence\DTOs\UsageStatistics;
use App\Domains\Intelligence\Exceptions\ContextOverflowException;

class TokenManager
{
    public function __construct(
        private readonly TokenCounter $tokenCounter,
    ) {}

    public function estimatePromptSize(array|string $input): int
    {
        return $this->tokenCounter->estimate($input);
    }

    public function preventContextOverflow(ContextWindow $window, array|string $input): void
    {
        if ($this->estimatePromptSize($input) > $window->availableTokens()) {
            throw new ContextOverflowException('The assembled prompt exceeds the configured context window.');
        }
    }

    /**
     * @param list<ChatMessage> $history
     * @return list<ChatMessage>
     */
    public function trimHistory(array $history, ContextWindow $window): array
    {
        $trimmed = $history;

        while (count($trimmed) > 1 && $this->estimatePromptSize(array_map(
            static fn (ChatMessage $message): array => $message->toArray(),
            $trimmed,
        )) > $window->availableTokens()) {
            array_shift($trimmed);
        }

        return $trimmed;
    }

    public function completionSize(string $content): int
    {
        return $this->estimatePromptSize($content);
    }

    public function usageFor(string $content, ?int $inputTokens = null, ?int $latencyMs = null): UsageStatistics
    {
        $outputTokens = $this->completionSize($content);

        return new UsageStatistics(
            inputTokens: $inputTokens ?? 0,
            outputTokens: $outputTokens,
            totalTokens: ($inputTokens ?? 0) + $outputTokens,
            latencyMs: $latencyMs,
        );
    }
}
