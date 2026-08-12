<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Services;

use App\Domains\Intelligence\DTOs\UsageStatistics;
use App\Domains\Intelligence\Models\Conversation;
use App\Domains\Intelligence\Models\ConversationMessage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UsageTrackingService
{
    public function track(
        string $provider,
        string $model,
        UsageStatistics $usage,
        ?Conversation $conversation = null,
        ?ConversationMessage $message = null,
        array $metadata = [],
    ): void {
        DB::table('provider_usage')->insert([
            'id' => (string) Str::uuid(),
            'conversation_id' => $conversation?->getKey(),
            'conversation_message_id' => $message?->getKey(),
            'provider' => $provider,
            'model' => $model,
            'input_tokens' => $usage->inputTokens,
            'output_tokens' => $usage->outputTokens,
            'duration_ms' => $usage->latencyMs,
            'success' => $usage->success,
            'error_message' => $usage->error,
            'cost' => $usage->cost,
            'metadata' => json_encode($metadata, JSON_UNESCAPED_SLASHES),
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);
    }

    public function summary(): array
    {
        $usage = DB::table('provider_usage')
            ->selectRaw('COUNT(*) as interactions')
            ->selectRaw('COALESCE(SUM(input_tokens), 0) as input_tokens')
            ->selectRaw('COALESCE(SUM(output_tokens), 0) as output_tokens')
            ->selectRaw('COUNT(CASE WHEN success = 0 THEN 1 END) as failures')
            ->first();

        return [
            'interactions' => (int) ($usage->interactions ?? 0),
            'input_tokens' => (int) ($usage->input_tokens ?? 0),
            'output_tokens' => (int) ($usage->output_tokens ?? 0),
            'failures' => (int) ($usage->failures ?? 0),
        ];
    }
}
