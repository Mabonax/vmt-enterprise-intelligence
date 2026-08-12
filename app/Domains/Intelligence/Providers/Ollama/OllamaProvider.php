<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Providers\Ollama;

use App\Domains\Intelligence\Contracts\AiProvider;
use App\Domains\Intelligence\Contracts\TokenCounter;
use App\Domains\Intelligence\DTOs\ChatMessage;
use App\Domains\Intelligence\DTOs\ChatRequest;
use App\Domains\Intelligence\DTOs\ChatResponse;
use App\Domains\Intelligence\DTOs\ToolCall;
use App\Domains\Intelligence\DTOs\UsageStatistics;
use App\Domains\Intelligence\Enums\ChatRole;
use App\Domains\Intelligence\Exceptions\ProviderException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class OllamaProvider implements AiProvider, TokenCounter
{
    public function key(): string
    {
        return 'ollama';
    }

    public function chat(ChatRequest $request): ChatResponse
    {
        $response = $this->request()->post('/chat', [
            'model' => $request->model,
            'messages' => array_map(
                static fn (ChatMessage $message): array => [
                    'role' => $message->role->value,
                    'content' => $message->content,
                ],
                $request->messages,
            ),
            'stream' => false,
            'tools' => $request->metadata['tools'] ?? null,
            'format' => $request->metadata['format'] ?? null,
            'keep_alive' => $request->metadata['keep_alive'] ?? config('intelligence.providers.ollama.keep_alive'),
            'options' => $request->metadata['options'] ?? [],
        ]);

        $payload = $this->decode($response);
        $content = (string) data_get($payload, 'message.content', '');
        $usage = $this->usageFromPayload($payload, $request->messages, $content);

        return new ChatResponse(
            message: new ChatMessage(
                role: ChatRole::Assistant,
                content: $content,
                usage: $usage,
                metadata: [
                    'provider' => $this->key(),
                    'done' => (bool) ($payload['done'] ?? true),
                    'done_reason' => $payload['done_reason'] ?? null,
                    'thinking' => data_get($payload, 'message.thinking'),
                ],
            ),
            usage: $usage,
            toolCalls: $this->toolCallsFromPayload($payload),
            metadata: [
                'provider' => $this->key(),
                'model' => $payload['model'] ?? $request->model,
                'created_at' => $payload['created_at'] ?? null,
            ],
        );
    }

    public function stream(ChatRequest $request): iterable
    {
        $response = $this->request()
            ->withOptions(['stream' => true])
            ->post('/chat', [
                'model' => $request->model,
                'messages' => array_map(
                    static fn (ChatMessage $message): array => [
                        'role' => $message->role->value,
                        'content' => $message->content,
                    ],
                    $request->messages,
                ),
                'stream' => true,
                'tools' => $request->metadata['tools'] ?? null,
                'format' => $request->metadata['format'] ?? null,
                'keep_alive' => $request->metadata['keep_alive'] ?? config('intelligence.providers.ollama.keep_alive'),
                'options' => $request->metadata['options'] ?? [],
            ]);

        if ($response->failed()) {
            $this->throwProviderException($response);
        }

        $stream = $response->toPsrResponse()->getBody();
        $buffer = '';

        while (! $stream->eof()) {
            $buffer .= $stream->read(8192);

            while (($position = strpos($buffer, "\n")) !== false) {
                $line = trim(substr($buffer, 0, $position));
                $buffer = substr($buffer, $position + 1);

                if ($line === '') {
                    continue;
                }

                $payload = json_decode($line, true);

                if (! is_array($payload)) {
                    continue;
                }

                if (isset($payload['error'])) {
                    throw new ProviderException((string) $payload['error']);
                }

                yield [
                    'event' => (bool) ($payload['done'] ?? false) ? 'done' : 'token',
                    'payload' => $payload,
                ];
            }
        }
    }

    public function embeddings(array $input): array
    {
        $response = $this->request()->post('/embed', [
            'model' => (string) config('intelligence.providers.ollama.embedding_model', config('intelligence.default_model')),
            'input' => $input,
            'truncate' => true,
            'keep_alive' => config('intelligence.providers.ollama.keep_alive'),
        ]);

        return $this->decode($response);
    }

    public function models(): array
    {
        $response = $this->request()->get('/tags');
        $payload = $this->decode($response);

        return array_map(static function (array $model): array {
            $details = $model['details'] ?? [];
            $families = $details['families'] ?? [];
            $family = $details['family'] ?? null;
            $capabilities = array_values(array_filter([
                is_string($family) ? $family : null,
                ...array_filter(is_array($families) ? $families : [], 'is_string'),
            ]));

            return [
                'key' => $model['model'] ?? $model['name'] ?? 'unknown',
                'label' => $model['name'] ?? $model['model'] ?? 'Unknown',
                'supports_streaming' => true,
                'supports_embeddings' => collect($capabilities)->contains(fn (string $entry): bool => str_contains(strtolower($entry), 'embed')),
                'details' => $details,
                'size' => $model['size'] ?? null,
                'modified_at' => $model['modified_at'] ?? null,
            ];
        }, $payload['models'] ?? []);
    }

    public function health(): array
    {
        $startedAt = microtime(true);

        try {
            $versionResponse = $this->request()->get('/version');
            $versionPayload = $this->decode($versionResponse);
            $models = $this->models();

            return [
                'provider' => $this->key(),
                'status' => 'healthy',
                'checked_at' => now()->toIso8601String(),
                'latency_ms' => (int) ((microtime(true) - $startedAt) * 1000),
                'version' => $versionPayload['version'] ?? null,
                'models_available' => count($models),
                'endpoint' => rtrim((string) config('intelligence.providers.ollama.endpoint', 'http://localhost:11434/api'), '/'),
                'configuration_valid' => $this->configurationValid(),
            ];
        } catch (\Throwable $exception) {
            return [
                'provider' => $this->key(),
                'status' => 'unhealthy',
                'checked_at' => now()->toIso8601String(),
                'latency_ms' => (int) ((microtime(true) - $startedAt) * 1000),
                'endpoint' => rtrim((string) config('intelligence.providers.ollama.endpoint', 'http://localhost:11434/api'), '/'),
                'configuration_valid' => $this->configurationValid(),
                'error' => $exception->getMessage(),
            ];
        }
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

    private function request(): PendingRequest
    {
        $request = Http::baseUrl(rtrim((string) config('intelligence.providers.ollama.endpoint', 'http://localhost:11434/api'), '/'))
            ->acceptJson()
            ->asJson()
            ->timeout((int) config('intelligence.providers.ollama.timeout', config('intelligence.timeout', 30)))
            ->connectTimeout((int) config('intelligence.providers.ollama.connect_timeout', 5))
            ->retry(
                (int) config('intelligence.providers.ollama.retry_attempts', 1),
                (int) config('intelligence.providers.ollama.retry_sleep_milliseconds', 200),
            );

        $apiKey = (string) config('intelligence.providers.ollama.api_key', '');

        if ($apiKey !== '') {
            $request = $request->withToken($apiKey);
        }

        return $request;
    }

    /**
     * @return array<string, mixed>
     */
    private function decode(Response $response): array
    {
        if ($response->failed()) {
            $this->throwProviderException($response);
        }

        return $response->json() ?? [];
    }

    private function throwProviderException(Response $response): never
    {
        $payload = $response->json();
        $message = is_array($payload) && isset($payload['error'])
            ? (string) $payload['error']
            : sprintf('Ollama request failed with status [%d].', $response->status());

        throw new ProviderException($message);
    }

    /**
     * @param array<string, mixed> $payload
     * @param list<ChatMessage> $messages
     */
    private function usageFromPayload(array $payload, array $messages, string $content): UsageStatistics
    {
        $inputTokens = (int) ($payload['prompt_eval_count'] ?? $this->estimateTokens(array_map(
            static fn (ChatMessage $message): array => ['role' => $message->role->value, 'content' => $message->content],
            $messages,
        )));
        $outputTokens = (int) ($payload['eval_count'] ?? $this->estimateTokens($content));

        return new UsageStatistics(
            inputTokens: $inputTokens,
            outputTokens: $outputTokens,
            totalTokens: $inputTokens + $outputTokens,
            latencyMs: isset($payload['total_duration']) ? (int) round(((int) $payload['total_duration']) / 1_000_000) : null,
            success: true,
        );
    }

    /**
     * @param array<string, mixed> $payload
     * @return list<ToolCall>
     */
    private function toolCallsFromPayload(array $payload): array
    {
        $toolCalls = data_get($payload, 'message.tool_calls', []);

        if (! is_array($toolCalls)) {
            return [];
        }

        return array_values(array_filter(array_map(static function (mixed $toolCall): ?ToolCall {
            if (! is_array($toolCall)) {
                return null;
            }

            $function = $toolCall['function'] ?? [];

            if (! is_array($function) || ! isset($function['name'])) {
                return null;
            }

            return new ToolCall(
                name: (string) $function['name'],
                arguments: is_array($function['arguments'] ?? null) ? $function['arguments'] : [],
                metadata: isset($toolCall['id']) ? ['id' => $toolCall['id']] : [],
            );
        }, $toolCalls)));
    }

    private function configurationValid(): bool
    {
        return str_starts_with((string) config('intelligence.providers.ollama.endpoint', ''), 'http');
    }
}
