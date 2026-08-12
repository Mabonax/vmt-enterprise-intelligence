<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\DTOs;

use App\Domains\Intelligence\Enums\ChatRole;
use App\Domains\Intelligence\Enums\MessageType;

final readonly class ChatMessage
{
    /**
     * @param list<Citation> $citations
     * @param list<ToolCall> $toolCalls
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public ChatRole $role,
        public string $content,
        public MessageType $type = MessageType::Text,
        public array $citations = [],
        public array $toolCalls = [],
        public ?UsageStatistics $usage = null,
        public array $metadata = [],
    ) {}

    /**
     * @return array{role: string, content: string, type: string, citations: list<array<string, mixed>>, tool_calls: list<array<string, mixed>>, usage: array<string, mixed>|null, metadata: array<string, mixed>}
     */
    public function toArray(): array
    {
        return [
            'role' => $this->role->value,
            'content' => $this->content,
            'type' => $this->type->value,
            'citations' => array_map(
                static fn (Citation $citation): array => $citation->toArray(),
                $this->citations,
            ),
            'tool_calls' => array_map(
                static fn (ToolCall $toolCall): array => $toolCall->toArray(),
                $this->toolCalls,
            ),
            'usage' => $this->usage?->toArray(),
            'metadata' => $this->metadata,
        ];
    }
}
