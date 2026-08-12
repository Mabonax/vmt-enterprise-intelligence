<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Gateway\Services;

use Illuminate\Auth\Access\AuthorizationException;

class ProviderAllowlistService
{
    public function assertAllowed(string $providerKey): void
    {
        $allowlist = collect(config('gateway.provider_allowlist', []));

        if ($allowlist->isNotEmpty() && ! $allowlist->contains($providerKey)) {
            throw new AuthorizationException("Provider [{$providerKey}] is not allowlisted for this gateway.");
        }

        $cloudEnabled = (bool) config('gateway.cloud_providers_enabled', false);
        $localProviders = collect(config('gateway.local_providers', ['ollama', 'lmstudio']));

        if (! $cloudEnabled && ! $localProviders->contains($providerKey)) {
            throw new AuthorizationException("Provider [{$providerKey}] is blocked by the local-first egress policy.");
        }
    }
}
