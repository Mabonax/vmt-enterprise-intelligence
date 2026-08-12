<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Tools;

use App\Domains\Intelligence\Attributes\ToolDefinition;
use App\Domains\Intelligence\Contracts\IntelligenceTool;
use App\Domains\Intelligence\DTOs\ToolContext;

#[ToolDefinition(slug: 'conversation_summary', category: 'conversation', tags: ['safe', 'summary'])]
class ConversationSummaryTool implements IntelligenceTool
{
    public function name(): string
    {
        return 'Conversation Summary';
    }

    public function slug(): string
    {
        return 'conversation_summary';
    }

    public function description(): string
    {
        return 'Returns a small summary of the active conversation.';
    }

    public function category(): string
    {
        return 'conversation';
    }

    public function permissions(): array
    {
        return [];
    }

    public function schema(): array
    {
        return ['type' => 'object'];
    }

    public function responseSchema(): array
    {
        return ['type' => 'object'];
    }

    public function execute(array $payload): mixed
    {
        /** @var ToolContext|null $context */
        $context = $payload['_tool_context'] ?? null;
        $conversation = $context?->conversation;

        return [
            'conversation_id' => $conversation?->id,
            'title' => $conversation?->title,
            'provider' => $conversation?->provider,
            'model' => $conversation?->model,
        ];
    }
}
