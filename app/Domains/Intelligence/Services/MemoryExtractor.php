<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Services;

use App\Domains\Intelligence\Enums\MemoryType;
use App\Domains\Intelligence\Models\Conversation;
use App\Domains\Intelligence\Models\ConversationMessage;
use App\Models\User;

class MemoryExtractor
{
    public function extract(User $user, Conversation $conversation, ConversationMessage $message): array
    {
        $content = trim($message->content);

        if ($content === '') {
            return [];
        }

        return [[
            'created_by' => $user->id,
            'conversation_id' => $conversation->id,
            'conversation_message_id' => $message->id,
            'agent_id' => $conversation->agent_id,
            'organization_id' => $user->organization_id,
            'subject_type' => User::class,
            'subject_id' => (string) $user->id,
            'memory_type' => str_contains(strtolower($content), 'prefer') ? MemoryType::Preference->value : MemoryType::Note->value,
            'visibility' => 'private',
            'content' => mb_substr($content, 0, 500),
            'importance_score' => min(100, max(25, (int) ceil(mb_strlen($content) / 4))),
            'confidence_score' => 0.60,
            'metadata' => ['source' => 'conversation_message'],
        ]];
    }
}
