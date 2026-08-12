<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Services;

use App\Domains\Intelligence\Contracts\AiProvider;
use Illuminate\Contracts\Container\Container;
use RuntimeException;

class ProviderManager
{
    /**
     * @param list<class-string<AiProvider>> $providerClasses
     */
    public function __construct(
        private readonly Container $container,
        private readonly array $providerClasses,
    ) {}

    public function resolve(?string $key = null): AiProvider
    {
        $providerKey = $key ?? (string) config('intelligence.default_provider');

        foreach ($this->providerClasses as $providerClass) {
            /** @var AiProvider $provider */
            $provider = $this->container->make($providerClass);

            if ($provider->key() === $providerKey) {
                return $provider;
            }
        }

        throw new RuntimeException("No intelligence provider is registered for [{$providerKey}].");
    }

    /**
     * @return list<AiProvider>
     */
    public function all(): array
    {
        return array_map(
            fn (string $providerClass): AiProvider => $this->container->make($providerClass),
            $this->providerClasses,
        );
    }
}
