<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Services;

use App\Domains\Intelligence\Contracts\PromptBuilder;
use App\Domains\Intelligence\DTOs\PromptContext;
use App\Domains\Intelligence\Enums\ChatRole;

class PromptAssembler implements PromptBuilder
{
    public function build(PromptContext $context): array
    {
        $messages = [
            [
                'role' => ChatRole::System->value,
                'content' => $context->systemPrompt,
            ],
        ];

        if ($context->pinnedContext !== []) {
            $messages[] = [
                'role' => ChatRole::System->value,
                'content' => 'Pinned context: '.json_encode($context->pinnedContext, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
            ];
        }

        if ($context->manualContext !== []) {
            $messages[] = [
                'role' => ChatRole::System->value,
                'content' => 'Manual context: '.json_encode($context->manualContext, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
            ];
        }

        if ($context->runtimeContext !== []) {
            $messages[] = [
                'role' => ChatRole::System->value,
                'content' => 'Runtime context: '.json_encode($context->runtimeContext, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
            ];
        }

        if ($context->toolDefinitions !== []) {
            $messages[] = [
                'role' => ChatRole::System->value,
                'content' => 'Available tools: '.json_encode($context->toolDefinitions, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
            ];
        }

        foreach ($context->conversationHistory as $message) {
            $messages[] = [
                'role' => $message->role->value,
                'content' => $message->content,
            ];
        }

        if ($context->userPrompt !== null) {
            $messages[] = [
                'role' => ChatRole::User->value,
                'content' => $context->userPrompt,
            ];
        }

        return $messages;
    }
}
