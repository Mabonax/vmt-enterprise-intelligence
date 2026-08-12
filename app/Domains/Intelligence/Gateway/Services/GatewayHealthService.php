<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Gateway\Services;

use App\Domains\Intelligence\Gateway\Models\AiProviderProfile;
use App\Domains\Intelligence\Gateway\Models\ModelCatalog;
use App\Domains\Intelligence\Gateway\Models\ProviderHealthCheck;
use App\Domains\Intelligence\Services\ProviderManager;

class GatewayHealthService
{
    public function __construct(
        private readonly ProviderManager $providers,
    ) {}

    public function status(): array
    {
        $provider = $this->providers->resolve((string) config('gateway.default_provider', config('intelligence.default_provider')));
        $health = $provider->health();
        $models = $provider->models();

        $profile = AiProviderProfile::query()->updateOrCreate(
            ['provider_key' => $provider->key()],
            [
                'display_name' => ucfirst($provider->key()),
                'status' => (string) ($health['status'] ?? 'unknown'),
                'capabilities' => [
                    'streaming' => true,
                    'embeddings' => true,
                ],
                'configuration' => [
                    'endpoint' => config('intelligence.providers.ollama.endpoint'),
                    'default_model' => config('intelligence.default_model'),
                ],
                'metadata' => $health,
                'last_checked_at' => now(),
                'last_latency_ms' => $health['latency_ms'] ?? null,
            ],
        );

        foreach ($models as $model) {
            ModelCatalog::query()->updateOrCreate(
                ['model_key' => (string) $model['key']],
                [
                    'provider_profile_id' => $profile->getKey(),
                    'category' => ($model['supports_embeddings'] ?? false) ? 'embedding' : 'chat',
                    'is_default' => (string) $model['key'] === (string) config('intelligence.default_model'),
                    'metadata' => $model,
                ],
            );
        }

        ProviderHealthCheck::query()->create([
            'provider_key' => $provider->key(),
            'status' => (string) ($health['status'] ?? 'unknown'),
            'latency_ms' => $health['latency_ms'] ?? null,
            'available_models' => count($models),
            'payload' => $health,
            'checked_at' => now(),
        ]);

        return [
            'gateway' => [
                'status' => ($health['status'] ?? 'unknown') === 'healthy' ? 'healthy' : 'degraded',
                'default_provider' => config('gateway.default_provider'),
                'audit_enabled' => (bool) config('gateway.audit_enabled', true),
                'cloud_providers_enabled' => (bool) config('gateway.cloud_providers_enabled', false),
                'request_timeout_seconds' => (int) config('gateway.request_timeout', 30),
            ],
            'provider' => $health,
            'runtime' => [
                'provider' => $provider->key(),
                'models' => $models,
                'models_available' => count($models),
            ],
        ];
    }
}
