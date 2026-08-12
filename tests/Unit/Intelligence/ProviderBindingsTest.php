<?php

declare(strict_types=1);

namespace Tests\Unit\Intelligence;

use App\Domains\Intelligence\Contracts\AiProvider;
use App\Domains\Intelligence\Contracts\EmbeddingProvider;
use App\Domains\Intelligence\Contracts\StreamingProvider;
use App\Domains\Intelligence\Contracts\TokenCounter;
use Tests\TestCase;

class ProviderBindingsTest extends TestCase
{
    public function test_default_provider_bindings_resolve_from_the_container(): void
    {
        $provider = app(AiProvider::class);

        $this->assertSame('ollama', $provider->key());
        $this->assertSame($provider::class, app(EmbeddingProvider::class)::class);
        $this->assertSame($provider::class, app(StreamingProvider::class)::class);
        $this->assertSame($provider::class, app(TokenCounter::class)::class);
    }
}
