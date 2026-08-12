<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\DTOs;

final readonly class PromptContext
{
    /**
     * @param list<ChatMessage> $conversationHistory
     * @param array<string, mixed> $manualContext
     * @param array<string, mixed> $pinnedContext
     * @param array<string, mixed> $runtimeContext
     * @param list<array{name: string, description: string, schema: array<string, mixed>}> $toolDefinitions
     */
    public function __construct(
        public string $systemPrompt,
        public array $conversationHistory = [],
        public array $manualContext = [],
        public array $pinnedContext = [],
        public array $runtimeContext = [],
        public array $toolDefinitions = [],
        public ?string $userPrompt = null,
    ) {}
}
