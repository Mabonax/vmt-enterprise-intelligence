<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Services;

use App\Domains\Intelligence\Contracts\ConversationRepository;
use App\Domains\Intelligence\DTOs\ChatMessage;
use App\Domains\Intelligence\Enums\ConversationStatus;
use App\Domains\Intelligence\Models\Conversation;
use App\Domains\Intelligence\Models\ConversationMessage;

class ConversationManager
{
    public function __construct(
        private readonly ConversationRepository $repository,
        private readonly UsageTrackingService $usageTracking,
    ) {}

    public function createConversation(
        int $userId,
        string $title,
        string $provider,
        string $model,
        array $metadata = [],
    ): Conversation {
        return $this->repository->create([
            'user_id' => $userId,
            'title' => $title,
            'provider' => $provider,
            'model' => $model,
            'status' => ConversationStatus::Active,
            'metadata' => $metadata,
        ]);
    }

    public function addMessage(Conversation $conversation, ChatMessage $message): ConversationMessage
    {
        $record = $this->repository->addMessage($conversation, $message);

        if ($message->usage !== null) {
            $this->usageTracking->track(
                provider: $conversation->provider,
                model: $conversation->model,
                usage: $message->usage,
                conversation: $conversation,
                message: $record,
            );
        }

        return $record;
    }

    public function editMessage(ConversationMessage $message, string $content, array $metadata = []): ConversationMessage
    {
        return $this->repository->updateMessage($message, [
            'content' => $content,
            'metadata' => array_merge($message->metadata ?? [], $metadata),
        ]);
    }

    public function deleteConversation(Conversation $conversation): void
    {
        $this->repository->delete($conversation);
    }

    public function archiveConversation(Conversation $conversation): Conversation
    {
        return $this->repository->archive($conversation);
    }

    public function renameConversation(Conversation $conversation, string $title): Conversation
    {
        return $this->repository->update($conversation, ['title' => $title]);
    }

    public function switchProvider(Conversation $conversation, string $provider, string $model): Conversation
    {
        return $this->repository->update($conversation, [
            'provider' => $provider,
            'model' => $model,
        ]);
    }

    public function usageStatistics(?string $conversationId = null): array
    {
        return $this->repository->usageTotals($conversationId);
    }
}
