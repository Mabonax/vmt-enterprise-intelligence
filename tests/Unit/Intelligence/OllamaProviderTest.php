<?php

declare(strict_types=1);

namespace Tests\Unit\Intelligence;

use App\Domains\Intelligence\DTOs\ChatMessage;
use App\Domains\Intelligence\DTOs\ChatRequest;
use App\Domains\Intelligence\Enums\ChatRole;
use App\Domains\Intelligence\Enums\ProviderType;
use App\Domains\Intelligence\Providers\Ollama\OllamaProvider;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OllamaProviderTest extends TestCase
{
    public function test_ollama_provider_executes_real_chat_transport(): void
    {
        Http::fake([
            'http://localhost:11434/api/chat' => Http::response([
                'model' => 'runtime-placeholder',
                'message' => [
                    'role' => 'assistant',
                    'content' => 'Transport is live.',
                ],
                'done' => true,
                'total_duration' => 350000000,
                'prompt_eval_count' => 7,
                'eval_count' => 4,
            ]),
        ]);

        $provider = app(OllamaProvider::class);
        $response = $provider->chat(new ChatRequest(
            provider: ProviderType::Ollama,
            model: 'runtime-placeholder',
            messages: [
                new ChatMessage(role: ChatRole::User, content: 'Test the transport.'),
            ],
        ));

        $this->assertSame('Transport is live.', $response->message->content);
        $this->assertSame(7, $response->usage->inputTokens);
        $this->assertSame(4, $response->usage->outputTokens);
        $this->assertSame(350, $response->usage->latencyMs);
    }

    public function test_ollama_provider_discovers_models_and_health(): void
    {
        Http::fake([
            'http://localhost:11434/api/version' => Http::response(['version' => '0.12.6']),
            'http://localhost:11434/api/tags' => Http::response([
                'models' => [
                    [
                        'name' => 'qwen3:8b',
                        'model' => 'qwen3:8b',
                        'size' => 123,
                        'details' => [
                            'family' => 'qwen3',
                            'families' => ['qwen3'],
                        ],
                    ],
                ],
            ]),
        ]);

        $provider = app(OllamaProvider::class);
        $health = $provider->health();
        $models = $provider->models();

        $this->assertSame('healthy', $health['status']);
        $this->assertSame(1, $health['models_available']);
        $this->assertCount(1, $models);
        $this->assertSame('qwen3:8b', $models[0]['key']);
    }
}
