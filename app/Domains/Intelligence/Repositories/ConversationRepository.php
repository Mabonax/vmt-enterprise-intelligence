<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Repositories;

use App\Domains\Intelligence\Contracts\ConversationRepository as ConversationRepositoryContract;
use App\Domains\Intelligence\DTOs\ChatMessage;
use App\Domains\Intelligence\Enums\ConversationStatus;
use App\Domains\Intelligence\Models\Conversation;
use App\Domains\Intelligence\Models\ConversationMessage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ConversationRepository implements ConversationRepositoryContract
{
    public function create(array $attributes): Conversation
    {
        return Conversation::query()->create($attributes);
    }

    public function find(string $conversationId): ?Conversation
    {
        return Conversation::query()
            ->with([
                'messages' => static fn ($query) => $query->orderBy('created_at'),
                'attachments',
            ])
            ->find($conversationId);
    }

    public function update(Conversation $conversation, array $attributes): Conversation
    {
        $conversation->fill($attributes)->save();

        return $conversation->refresh();
    }

    public function archive(Conversation $conversation): Conversation
    {
        return $this->update($conversation, [
            'status' => ConversationStatus::Archived,
            'archived_at' => now(),
        ]);
    }

    public function delete(Conversation $conversation): void
    {
        $conversation->delete();
    }

    public function addMessage(Conversation $conversation, ChatMessage $message): ConversationMessage
    {
        $record = new ConversationMessage([
            'role' => $message->role,
            'type' => $message->type,
            'content' => $message->content,
            'citations' => array_map(static fn ($citation): array => $citation->toArray(), $message->citations),
            'tool_calls' => array_map(static fn ($toolCall): array => $toolCall->toArray(), $message->toolCalls),
            'input_tokens' => $message->usage?->inputTokens,
            'output_tokens' => $message->usage?->outputTokens,
            'latency_ms' => $message->usage?->latencyMs,
            'metadata' => $message->metadata,
        ]);

        $conversation->messages()->save($record);

        return $record->refresh();
    }

    public function updateMessage(ConversationMessage $message, array $attributes): ConversationMessage
    {
        $message->fill($attributes)->save();

        return $message->refresh();
    }

    public function usageTotals(?string $conversationId = null): array
    {
        $query = DB::table('provider_usage');

        if ($conversationId !== null) {
            $query->where('conversation_id', $conversationId);
        }

        $totals = $query->selectRaw('COALESCE(SUM(input_tokens), 0) as input_tokens')
            ->selectRaw('COALESCE(SUM(output_tokens), 0) as output_tokens')
            ->selectRaw('COALESCE(SUM(duration_ms), 0) as duration_ms')
            ->first();

        return [
            'input_tokens' => (int) ($totals->input_tokens ?? 0),
            'output_tokens' => (int) ($totals->output_tokens ?? 0),
            'total_tokens' => (int) (($totals->input_tokens ?? 0) + ($totals->output_tokens ?? 0)),
            'duration_ms' => (int) ($totals->duration_ms ?? 0),
        ];
    }

    public function paginateForUser(int $userId, int $perPage = 15, ?ConversationStatus $status = null): LengthAwarePaginator
    {
        return Conversation::query()
            ->withCount('messages')
            ->where('user_id', $userId)
            ->when($status !== null, static fn ($query) => $query->where('status', $status->value))
            ->latest()
            ->paginate($perPage);
    }
}
