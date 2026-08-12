<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Providers;

use App\Domains\Intelligence\Contracts\AiProvider;
use App\Domains\Intelligence\Contracts\TokenCounter;
use App\Domains\Intelligence\DTOs\ChatMessage;
use App\Domains\Intelligence\DTOs\ChatRequest;
use App\Domains\Intelligence\DTOs\ChatResponse;
use App\Domains\Intelligence\DTOs\UsageStatistics;
use App\Domains\Intelligence\Enums\ChatRole;

abstract class AbstractStubProvider implements AiProvider, TokenCounter
{
    abstract public function key(): string;

    public function chat(ChatRequest $request): ChatResponse
    {
        $content = sprintf(
            '%s provider stub acknowledged model [%s].',
            strtoupper($this->key()),
            $request->model,
        );

        $usage = new UsageStatistics(
            inputTokens: $this->estimate($request->messages),
            outputTokens: $this->estimate($content),
            totalTokens: $this->estimate($request->messages) + $this->estimate($content),
            latencyMs: 0,
        );

        $message = new ChatMessage(
            role: ChatRole::Assistant,
            content: $content,
            usage: $usage,
            metadata: ['stub' => true],
        );

        return new ChatResponse(
            message: $message,
            usage: $usage,
            metadata: ['provider' => $this->key(), 'stub' => true],
        );
    }

    public function stream(ChatRequest $request): iterable
    {
        yield [
            'event' => 'stub',
            'provider' => $this->key(),
            'model' => $request->model,
            'message' => 'Streaming is scaffolded but not implemented in Phase 2.',
        ];
    }

    public function embeddings(array $input): array
    {
        return [
            'provider' => $this->key(),
            'status' => 'stubbed',
            'vectors' => array_fill(0, count($input), []),
        ];
    }

    public function models(): array
    {
        return [
            [
                'key' => "{$this->key()}-placeholder",
                'label' => strtoupper($this->key()).' Placeholder',
                'supports_streaming' => true,
                'supports_embeddings' => false,
            ],
        ];
    }

    public function health(): array
    {
        return [
            'provider' => $this->key(),
            'status' => 'stub',
            'checked_at' => now()->toIso8601String(),
        ];
    }

    public function estimateTokens(array|string $input): int
    {
        return $this->estimate($input);
    }

    public function estimate(array|string $input): int
    {
        $text = is_array($input) ? json_encode($input, JSON_UNESCAPED_SLASHES) ?: '' : $input;

        return max(1, (int) ceil(str_word_count($text) * 1.33));
    }
}
