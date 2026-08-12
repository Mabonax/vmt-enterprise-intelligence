<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Services;

use App\Domains\Intelligence\Models\Conversation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ConversationExportService
{
    public function export(Conversation $conversation, string $format = 'json'): array
    {
        $payload = [
            'conversation' => $conversation->toArray(),
            'messages' => $conversation->messages()->get()->toArray(),
            'attachments' => $conversation->attachments()->get()->toArray(),
        ];

        DB::table('conversation_exports')->insert([
            'id' => (string) Str::uuid(),
            'conversation_id' => $conversation->getKey(),
            'format' => $format,
            'status' => 'generated',
            'payload' => json_encode($payload, JSON_UNESCAPED_SLASHES),
            'metadata' => json_encode(['generated_at' => now()->toIso8601String()], JSON_UNESCAPED_SLASHES),
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        return $payload;
    }
}
