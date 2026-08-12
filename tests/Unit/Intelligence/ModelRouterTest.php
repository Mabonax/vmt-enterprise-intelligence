<?php

declare(strict_types=1);

namespace Tests\Unit\Intelligence;

use App\Domains\Intelligence\Enums\ModelCapability;
use App\Domains\Intelligence\Models\ModelRoutingRule;
use App\Domains\Intelligence\Services\ModelRouter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelRouterTest extends TestCase
{
    use RefreshDatabase;

    public function test_router_uses_priority_and_fallback(): void
    {
        ModelRoutingRule::query()->create([
            'provider' => 'ollama',
            'model' => 'economy-model',
            'capability' => ModelCapability::Chat,
            'priority' => 10,
            'enabled' => true,
            'fallback_provider' => 'gemini',
            'fallback_model' => 'fallback-model',
        ]);

        ModelRoutingRule::query()->create([
            'provider' => 'ollama',
            'model' => 'premium-model',
            'capability' => ModelCapability::Chat,
            'priority' => 1,
            'enabled' => true,
            'fallback_provider' => 'gemini',
            'fallback_model' => 'fallback-model',
        ]);

        $resolved = app(ModelRouter::class)->resolve(ModelCapability::Chat, 'ollama');

        $this->assertSame('premium-model', $resolved['model']);
        $this->assertSame('gemini', $resolved['fallback_provider']);
    }
}
