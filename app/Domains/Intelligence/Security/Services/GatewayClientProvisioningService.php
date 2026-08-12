<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Security\Services;

use App\Domains\Intelligence\Security\DTOs\GatewayApiKeyMaterialData;
use App\Domains\Intelligence\Security\Enums\GatewayAuthMethod;
use App\Domains\Intelligence\Security\Enums\GatewayClientStatus;
use App\Domains\Intelligence\Security\Enums\GatewayCredentialStatus;
use App\Domains\Intelligence\Security\Models\GatewayApiCredential;
use App\Domains\Intelligence\Security\Models\GatewayClient;
use App\Domains\Intelligence\Security\Models\GatewayPolicy;
use App\Domains\Intelligence\Security\Models\GatewayQuota;
use App\Domains\Intelligence\Security\Models\GatewayRateLimit;
use App\Domains\Intelligence\Security\Models\GatewayScope;
use App\Domains\Intelligence\Security\Models\GatewayTenant;
use App\Domains\Intelligence\Security\Validators\AllowedIpValidator;
use App\Domains\Intelligence\Security\Validators\AllowedOriginValidator;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class GatewayClientProvisioningService
{
    public function __construct(
        private readonly GatewayKeyHasher $hasher,
        private readonly AllowedOriginValidator $origins,
        private readonly AllowedIpValidator $ips,
    ) {}

    /**
     * @param array<string, mixed> $payload
     */
    public function createClient(array $payload): GatewayClient
    {
        $tenant = GatewayTenant::query()->findOrFail((string) $payload['gateway_tenant_id']);

        $client = GatewayClient::query()->create([
            'gateway_tenant_id' => $tenant->getKey(),
            'organization_id' => $payload['organization_id'] ?? $tenant->organization_id,
            'connected_erp_id' => $payload['connected_erp_id'] ?? null,
            'client_type' => $payload['client_type'] ?? 'erp',
            'name' => $payload['name'],
            'description' => $payload['description'] ?? null,
            'status' => $payload['status'] ?? GatewayClientStatus::Active->value,
            'environment' => $payload['environment'] ?? 'production',
            'allowed_origins' => $this->origins->validate($payload['allowed_origins'] ?? []),
            'allowed_ips' => $this->ips->validate($payload['allowed_ips'] ?? []),
            'rate_limit_per_minute' => $payload['rate_limit_per_minute'] ?? null,
            'rate_limit_per_hour' => $payload['rate_limit_per_hour'] ?? null,
            'rate_limit_per_day' => $payload['rate_limit_per_day'] ?? null,
            'daily_quota' => $payload['daily_quota'] ?? null,
            'concurrent_requests' => $payload['concurrent_requests'] ?? null,
            'burst_limit' => $payload['burst_limit'] ?? null,
            'enabled_providers' => array_values($payload['enabled_providers'] ?? []),
            'enabled_models' => array_values($payload['enabled_models'] ?? []),
            'enabled_capabilities' => array_values($payload['enabled_capabilities'] ?? []),
            'metadata' => $payload['metadata'] ?? [],
            'created_by' => $payload['created_by'] ?? null,
            'updated_by' => $payload['updated_by'] ?? null,
        ]);

        foreach (array_values($payload['scopes'] ?? []) as $scope) {
            GatewayScope::query()->create([
                'id' => (string) Str::uuid(),
                'gateway_tenant_id' => $tenant->getKey(),
                'gateway_client_id' => $client->getKey(),
                'scope' => (string) $scope,
                'status' => 'active',
            ]);
        }

        if (($payload['daily_quota'] ?? null) !== null) {
            GatewayQuota::query()->create([
                'id' => (string) Str::uuid(),
                'gateway_tenant_id' => $tenant->getKey(),
                'gateway_client_id' => $client->getKey(),
                'quota_type' => 'daily_tokens',
                'limit_value' => (int) $payload['daily_quota'],
                'consumed_value' => 0,
                'window_starts_at' => now()->startOfDay(),
                'window_ends_at' => now()->endOfDay(),
            ]);
        }

        foreach (['minute', 'hour', 'day'] as $window) {
            $key = 'rate_limit_per_'.$window;

            if (($payload[$key] ?? null) === null) {
                continue;
            }

            GatewayRateLimit::query()->create([
                'id' => (string) Str::uuid(),
                'gateway_tenant_id' => $tenant->getKey(),
                'gateway_client_id' => $client->getKey(),
                'window' => $window,
                'max_requests' => (int) $payload[$key],
                'burst_limit' => $payload['burst_limit'] ?? null,
                'concurrent_limit' => $payload['concurrent_requests'] ?? null,
            ]);
        }

        if (! empty($payload['policy'])) {
            GatewayPolicy::query()->create([
                'id' => (string) Str::uuid(),
                'gateway_tenant_id' => $tenant->getKey(),
                'name' => $payload['policy']['name'] ?? ($client->name.' default policy'),
                'policy_type' => $payload['policy']['policy_type'] ?? 'provider',
                'status' => $payload['policy']['status'] ?? 'active',
                'provider_rules' => $payload['policy']['provider_rules'] ?? [],
                'model_rules' => $payload['policy']['model_rules'] ?? [],
                'capability_rules' => $payload['policy']['capability_rules'] ?? [],
                'metadata' => ['gateway_client_id' => $client->getKey()],
            ]);
        }

        return $client;
    }

    public function issueKey(GatewayClient $client, GatewayAuthMethod $authMethod = GatewayAuthMethod::ApiKey, ?\DateTimeInterface $expiresAt = null): GatewayApiKeyMaterialData
    {
        $identifier = 'gk_'.Str::lower(Str::random(20));
        $apiKey = $identifier.'.'.Str::random(48);
        $apiSecret = 'gs_'.Str::random(64);
        $version = (int) $client->key_version;

        GatewayApiCredential::query()->create([
            'gateway_client_id' => $client->getKey(),
            'auth_method' => $authMethod->value,
            'key_identifier' => $identifier,
            'key_hash' => $this->hasher->hash($apiKey),
            'secret_hash' => $this->hasher->hash($apiSecret),
            'secret_ciphertext' => Crypt::encryptString($apiSecret),
            'version' => $version,
            'status' => GatewayCredentialStatus::Active->value,
            'expires_at' => $expiresAt,
            'metadata' => [],
        ]);

        return new GatewayApiKeyMaterialData(
            keyIdentifier: $identifier,
            apiKey: $apiKey,
            apiSecret: $apiSecret,
            version: $version,
            expiresAt: $expiresAt?->format(DATE_ATOM),
        );
    }

    public function revokeKey(string $clientId, ?string $keyIdentifier = null): int
    {
        $query = GatewayApiCredential::query()
            ->where('gateway_client_id', $clientId)
            ->where('status', GatewayCredentialStatus::Active->value);

        if ($keyIdentifier !== null) {
            $query->where('key_identifier', $keyIdentifier);
        }

        return $query->update([
            'status' => GatewayCredentialStatus::Revoked->value,
            'revoked_at' => now(),
        ]);
    }

    public function rotateKey(GatewayClient $client, ?string $keyIdentifier = null, ?\DateTimeInterface $expiresAt = null): GatewayApiKeyMaterialData
    {
        $this->revokeKey($client->getKey(), $keyIdentifier);
        $client->forceFill(['key_version' => $client->key_version + 1])->save();

        return $this->issueKey($client->refresh(), GatewayAuthMethod::ApiKey, $expiresAt);
    }
}
