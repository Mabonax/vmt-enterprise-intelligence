<?php

declare(strict_types=1);

namespace Tests\Feature\Intelligence;

use App\Domains\Intelligence\Gateway\Services\ProviderAllowlistService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GatewayMaterializationTest extends TestCase
{
    use RefreshDatabase;

    public function test_scaffold_provider_is_rejected_for_production_traffic(): void
    {
        config([
            'gateway.provider_allowlist' => ['ollama', 'lmstudio'],
            'gateway.local_providers' => ['ollama', 'lmstudio'],
            'gateway.stub_providers' => ['lmstudio'],
            'gateway.allow_stub_providers' => false,
            'gateway.cloud_providers_enabled' => false,
        ]);

        $this->expectException(AuthorizationException::class);
        $this->expectExceptionMessage('scaffold-only');

        app(ProviderAllowlistService::class)->assertAllowed('lmstudio');
    }

    public function test_health_is_ready_when_required_local_models_are_installed(): void
    {
        config([
            'gateway.default_provider' => 'ollama',
            'gateway.stub_providers' => ['lmstudio', 'openai', 'anthropic', 'gemini'],
            'gateway.allow_stub_providers' => false,
            'intelligence.default_model' => 'llama3.2:3b',
            'intelligence.knowledge.enabled' => true,
            'intelligence.providers.ollama.embedding_model' => 'embeddinggemma',
        ]);

        Http::fake([
            'http://localhost:11434/api/version' => Http::response([
                'version' => '0.0-test',
            ]),
            'http://localhost:11434/api/tags' => Http::response([
                'models' => [
                    [
                        'name' => 'llama3.2:3b',
                        'model' => 'llama3.2:3b',
                        'details' => ['family' => 'llama'],
                    ],
                    [
                        'name' => 'embeddinggemma:latest',
                        'model' => 'embeddinggemma:latest',
                        'details' => ['family' => 'embedding'],
                    ],
                ],
            ]),
        ]);

        $this->getJson(route('api.gateway.health'))
            ->assertOk()
            ->assertJsonPath('gateway.status', 'healthy')
            ->assertJsonPath('gateway.ready', true)
            ->assertJsonPath('readiness.ready', true)
            ->assertJsonPath('readiness.checks.provider_registered', true)
            ->assertJsonPath('readiness.checks.provider_healthy', true)
            ->assertJsonPath('readiness.checks.provider_eligible', true)
            ->assertJsonPath('readiness.checks.chat_model_available', true)
            ->assertJsonPath('readiness.checks.embedding_model_available', true);
    }

    public function test_health_is_degraded_when_default_chat_model_is_missing(): void
    {
        config([
            'gateway.default_provider' => 'ollama',
            'gateway.stub_providers' => [],
            'intelligence.default_model' => 'llama3.2:3b',
            'intelligence.knowledge.enabled' => true,
            'intelligence.providers.ollama.embedding_model' => 'embeddinggemma',
        ]);

        Http::fake([
            'http://localhost:11434/api/version' => Http::response([
                'version' => '0.0-test',
            ]),
            'http://localhost:11434/api/tags' => Http::response([
                'models' => [
                    [
                        'name' => 'embeddinggemma:latest',
                        'model' => 'embeddinggemma:latest',
                        'details' => ['family' => 'embedding'],
                    ],
                ],
            ]),
        ]);

        $this->getJson(route('api.gateway.health'))
            ->assertOk()
            ->assertJsonPath('gateway.status', 'degraded')
            ->assertJsonPath('gateway.ready', false)
            ->assertJsonPath('readiness.checks.chat_model_available', false)
            ->assertJsonPath('readiness.checks.embedding_model_available', true);
    }
}
