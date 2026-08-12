<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Gateway\Services;

use App\Domains\Connections\Models\ConnectedErp;
use App\Domains\Connections\Models\ErpApiKey;
use Illuminate\Auth\AuthenticationException;

class ErpClientAuthenticator
{
    /**
     * @return array{erp: ConnectedErp, apiKey: ErpApiKey}
     */
    public function authenticate(string $systemKey, string $rawKey): array
    {
        /** @var ConnectedErp|null $erp */
        $erp = ConnectedErp::query()
            ->where('system_key', $systemKey)
            ->where('status', 'active')
            ->first();

        if ($erp === null) {
            throw new AuthenticationException('The ERP client is not active or is not registered.');
        }

        /** @var ErpApiKey|null $apiKey */
        $apiKey = ErpApiKey::query()
            ->where('connected_erp_id', $erp->getKey())
            ->where('is_active', true)
            ->get()
            ->first(fn (ErpApiKey $candidate): bool => hash_equals($candidate->key_hash, hash('sha256', $rawKey)));

        if ($apiKey === null || ($apiKey->expires_at !== null && now()->greaterThan($apiKey->expires_at))) {
            throw new AuthenticationException('The ERP credential is invalid or expired.');
        }

        $apiKey->forceFill(['last_used_at' => now()])->save();

        return ['erp' => $erp, 'apiKey' => $apiKey];
    }
}
