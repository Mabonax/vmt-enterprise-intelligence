<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\DTOs;

final readonly class ChatResponse
{
    /**
     * @param list<Citation> $citations
     * @param list<ToolCall> $toolCalls
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public ChatMessage $message,
        public UsageStatistics $usage,
        public array $citations = [],
        public array $toolCalls = [],
        public array $metadata = [],
    ) {}
}
