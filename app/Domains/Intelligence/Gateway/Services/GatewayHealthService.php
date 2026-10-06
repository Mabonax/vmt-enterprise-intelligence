<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Gateway\Services;

use App\Domains\Intelligence\Gateway\Models\AiProviderProfile;
use App\Domains\Intelligence\Gateway\Models\ModelCatalog;
use App\Domains\Intelligence\Gateway\Models\ProviderHealthCheck;
use App\Domains\Intelligence\Services\ProviderManager;
use Throwable;

class GatewayHealthService
{
    public function __construct(
        private readonly ProviderManager $providers,
    ) {}

    public function status(): array
    {
        $providerKey = (string) config('gateway.default_provider', config('intelligence.default_provider'));

        try {
            $provider = $this->providers->resolve($providerKey);
        } catch (Throwable $exception) {
            return [
                'gateway' => [
                    'status' => 'degraded',
                    'ready' => false,
                    'default_provider' => $providerKey,
                    'audit_enabled' => (bool) config('gateway.audit_enabled', true),
                    'cloud_providers_enabled' => (bool) config('gateway.cloud_providers_enabled', false),
                    'request_timeout_seconds' => (int) config('gateway.request_timeout', 30),
                ],
                'provider' => [
                    'provider' => $providerKey,
                    'status' => 'unavailable',
                    'error' => $exception->getMessage(),
                ],
                'runtime' => [
                    'provider' => $providerKey,
                    'models' => [],
                    'models_available' => 0,
                ],
                'readiness' => [
                    'ready' => false,
                    'checks' => [
                        'provider_registered' => false,
                        'provider_healthy' => false,
                        'provider_eligible' => false,
                        'chat_model_available' => false,
                        'embedding_model_available' => false,
                    ],
                ],
            ];
        }

        $health = $provider->health();
        $models = ($health['status'] ?? null) === 'healthy' ? $provider->models() : [];

        $defaultModel = (string) config('intelligence.default_model');
        $embeddingModel = (string) config('intelligence.providers.ollama.embedding_model');
        $stubProviders = collect(config('gateway.stub_providers', []));
        $providerEligible = (bool) config('gateway.allow_stub_providers', false)
            || ! $stubProviders->contains($provider->key());
        $providerHealthy = ($health['status'] ?? 'unknown') === 'healthy';
        $chatModelAvailable = $this->modelAvailable($models, $defaultModel);
        $knowledgeEnabled = (bool) config('intelligence.knowledge.enabled', true);
        $embeddingModelAvailable = ! $knowledgeEnabled || $this->modelAvailable($models, $embeddingModel);
        $ready = $providerHealthy
            && $providerEligible
            && $chatModelAvailable
            && $embeddingModelAvailable;

        $profile = AiProviderProfile::query()->updateOrCreate(
            ['provider_key' => $provider->key()],
            [
                'display_name' => ucfirst($provider->key()),
                'status' => $ready ? 'healthy' : 'degraded',
                'capabilities' => [
                    'streaming' => true,
                    'embeddings' => true,
                ],
                'configuration' => [
                    'endpoint' => config('intelligence.providers.ollama.endpoint'),
                    'default_model' => $defaultModel,
                    'embedding_model' => $embeddingModel,
                ],
                'metadata' => array_merge($health, [
                    'ready' => $ready,
                    'chat_model_available' => $chatModelAvailable,
                    'embedding_model_available' => $embeddingModelAvailable,
                    'provider_eligible' => $providerEligible,
                ]),
                'last_checked_at' => now(),
                'last_latency_ms' => $health['latency_ms'] ?? null,
            ],
        );

        foreach ($models as $model) {
            ModelCatalog::query()->updateOrCreate(
                [
                    'provider_profile_id' => $profile->getKey(),
                    'model_key' => (string) $model['key'],
                ],
                [
                    'category' => ($model['supports_embeddings'] ?? false) ? 'embedding' : 'chat',
                    'is_default' => $this->modelMatches((string) $model['key'], $defaultModel),
                    'metadata' => $model,
                ],
            );
        }

        ProviderHealthCheck::query()->create([
            'provider_key' => $provider->key(),
            'status' => $ready ? 'healthy' : 'degraded',
            'latency_ms' => $health['latency_ms'] ?? null,
            'available_models' => count($models),
            'payload' => [
                'provider' => $health,
                'readiness' => [
                    'ready' => $ready,
                    'provider_eligible' => $providerEligible,
                    'chat_model_available' => $chatModelAvailable,
                    'embedding_model_available' => $embeddingModelAvailable,
                ],
            ],
            'checked_at' => now(),
        ]);

        return [
            'gateway' => [
                'status' => $ready ? 'healthy' : 'degraded',
                'ready' => $ready,
                'default_provider' => config('gateway.default_provider'),
                'audit_enabled' => (bool) config('gateway.audit_enabled', true),
                'cloud_providers_enabled' => (bool) config('gateway.cloud_providers_enabled', false),
                'request_timeout_seconds' => (int) config('gateway.request_timeout', 30),
            ],
            'provider' => $health,
            'runtime' => [
                'provider' => $provider->key(),
                'default_model' => $defaultModel,
                'embedding_model' => $embeddingModel,
                'models' => $models,
                'models_available' => count($models),
            ],
            'readiness' => [
                'ready' => $ready,
                'checks' => [
                    'provider_registered' => true,
                    'provider_healthy' => $providerHealthy,
                    'provider_eligible' => $providerEligible,
                    'chat_model_available' => $chatModelAvailable,
                    'embedding_model_available' => $embeddingModelAvailable,
                ],
            ],
        ];
    }

    /**
     * @param list<array<string, mixed>> $models
     */
    private function modelAvailable(array $models, string $configuredModel): bool
    {
        if ($configuredModel === '') {
            return false;
        }

        foreach ($models as $model) {
            $key = (string) ($model['key'] ?? '');

            if ($this->modelMatches($key, $configuredModel)) {
                return true;
            }
        }

        return false;
    }

    private function modelMatches(string $actual, string $configured): bool
    {
        if ($actual === $configured) {
            return true;
        }

        return str_contains($actual, ':')
            ? strstr($actual, ':', true) === $configured
            : false;
    }
}
