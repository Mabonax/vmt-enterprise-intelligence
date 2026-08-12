<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Contracts;

use App\Domains\Intelligence\DTOs\ChatMessage;
use App\Domains\Intelligence\Enums\ConversationStatus;
use App\Domains\Intelligence\Models\Conversation;
use App\Domains\Intelligence\Models\ConversationMessage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ConversationRepository
{
    public function create(array $attributes): Conversation;

    public function find(string $conversationId): ?Conversation;

    public function update(Conversation $conversation, array $attributes): Conversation;

    public function archive(Conversation $conversation): Conversation;

    public function delete(Conversation $conversation): void;

    public function addMessage(Conversation $conversation, ChatMessage $message): ConversationMessage;

    public function updateMessage(ConversationMessage $message, array $attributes): ConversationMessage;

    public function usageTotals(?string $conversationId = null): array;

    public function paginateForUser(int $userId, int $perPage = 15, ?ConversationStatus $status = null): LengthAwarePaginator;
}
