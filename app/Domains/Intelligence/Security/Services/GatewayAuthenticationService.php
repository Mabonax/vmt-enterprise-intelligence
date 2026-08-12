<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Security\Services;

use App\Domains\Connections\Models\ConnectedErp;
use App\Domains\Intelligence\Gateway\Services\ErpClientAuthenticator;
use App\Domains\Intelligence\Security\Contracts\GatewayAuthenticator;
use App\Domains\Intelligence\Security\DTOs\AuthenticatedGatewayClientData;
use App\Domains\Intelligence\Security\Enums\GatewayAuthMethod;
use App\Domains\Intelligence\Security\Exceptions\GatewayAuthenticationException;
use App\Domains\Intelligence\Security\Repositories\GatewayCredentialRepository;
use App\Domains\Intelligence\Security\Repositories\GatewayTenantRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class GatewayAuthenticationService implements GatewayAuthenticator
{
    public function __construct(
        private readonly ErpClientAuthenticator $legacy,
        private readonly GatewayCredentialRepository $credentials,
        private readonly GatewayTenantRepository $tenants,
        private readonly GatewayKeyHasher $hasher,
        private readonly GatewayJwtService $jwt,
        private readonly GatewayRequestSignatureService $signatures,
    ) {}

    public function authenticate(Request $request): AuthenticatedGatewayClientData
    {
        if ($request->header('X-ERP-System') !== null || $request->header('X-ERP-Key') !== null) {
            return $this->authenticateLegacy($request);
        }

        $bearer = $request->bearerToken();

        if (is_string($bearer) && substr_count($bearer, '.') === 2 && ! str_starts_with($bearer, 'gk_')) {
            return $this->authenticateJwt($bearer);
        }

        if (is_string($bearer) && $bearer !== '') {
            return $this->authenticateApiKey($bearer);
        }

        if ($request->header('X-Gateway-Key-Id') !== null && $request->header('X-Gateway-Signature') !== null) {
            return $this->authenticateHmac($request);
        }

        throw new GatewayAuthenticationException('No supported gateway authentication mechanism was provided.');
    }

    private function authenticateLegacy(Request $request): AuthenticatedGatewayClientData
    {
        $systemKey = (string) $request->header('X-ERP-System', $request->input('erp_system', ''));
        $rawKey = (string) $request->header('X-ERP-Key', '');

        if ($rawKey === '' && str_starts_with((string) $request->header('Authorization', ''), 'Bearer ')) {
            $rawKey = substr((string) $request->header('Authorization'), 7);
        }

        ['erp' => $erp] = $this->legacy->authenticate($systemKey, $rawKey);
        $tenant = $this->tenants->findByOrganization($erp->organization_id);

        return new AuthenticatedGatewayClientData(
            authMethod: GatewayAuthMethod::LegacySharedSecret,
            tenant: $tenant,
            client: null,
            credential: null,
            legacyErp: $erp,
            organizationId: $erp->organization_id,
            clientName: $erp->name,
            clientKey: $erp->system_key,
            scopes: array_values($erp->permissions ?? []),
            capabilities: array_values($erp->permissions ?? []),
        );
    }

    private function authenticateApiKey(string $apiKey): AuthenticatedGatewayClientData
    {
        [$identifier] = explode('.', $apiKey, 2);
        $credential = $this->credentials->findActiveByIdentifier($identifier);

        if ($credential === null || ! $this->hasher->matches($credential->key_hash, $apiKey)) {
            throw new GatewayAuthenticationException('The gateway API key is invalid.');
        }

        if ($credential->expires_at !== null && now()->greaterThan($credential->expires_at)) {
            throw new GatewayAuthenticationException('The gateway API key has expired.');
        }

        $client = $credential->client;
        $tenant = $client->tenant;

        $credential->forceFill(['last_used_at' => now()])->save();
        $client->forceFill(['last_used_at' => now()])->save();

        return new AuthenticatedGatewayClientData(
            authMethod: GatewayAuthMethod::ApiKey,
            tenant: $tenant,
            client: $client,
            credential: $credential,
            legacyErp: null,
            organizationId: (string) ($client->organization_id ?? $tenant->organization_id),
            clientName: $client->name,
            clientKey: $identifier,
            scopes: $this->scopesForClient($client->getKey()),
            capabilities: array_values($client->enabled_capabilities ?? []),
            providers: array_values($client->enabled_providers ?? []),
            models: array_values($client->enabled_models ?? []),
        );
    }

    private function authenticateHmac(Request $request): AuthenticatedGatewayClientData
    {
        $identifier = (string) $request->header('X-Gateway-Key-Id', '');
        $signature = (string) $request->header('X-Gateway-Signature', '');
        $timestamp = (string) $request->header('X-Gateway-Timestamp', '');
        $nonce = (string) $request->header('X-Gateway-Nonce', '');

        $credential = $this->credentials->findActiveByIdentifier($identifier);

        if ($credential === null || $credential->secret_ciphertext === null) {
            throw new GatewayAuthenticationException('The gateway signing key is invalid.');
        }

        $secret = Crypt::decryptString($credential->secret_ciphertext);
        $expected = $this->signatures->expectedSignature($request, $secret, $timestamp, $nonce);

        if (! hash_equals($expected, $signature)) {
            throw new GatewayAuthenticationException('The request signature is invalid.');
        }

        $client = $credential->client;
        $tenant = $client->tenant;
        $credential->forceFill(['last_used_at' => now()])->save();
        $client->forceFill(['last_used_at' => now()])->save();

        return new AuthenticatedGatewayClientData(
            authMethod: GatewayAuthMethod::Hmac,
            tenant: $tenant,
            client: $client,
            credential: $credential,
            legacyErp: null,
            organizationId: (string) ($client->organization_id ?? $tenant->organization_id),
            clientName: $client->name,
            clientKey: $identifier,
            scopes: $this->scopesForClient($client->getKey()),
            capabilities: array_values($client->enabled_capabilities ?? []),
            providers: array_values($client->enabled_providers ?? []),
            models: array_values($client->enabled_models ?? []),
        );
    }

    private function authenticateJwt(string $jwt): AuthenticatedGatewayClientData
    {
        $payload = $this->jwt->decode($jwt, (string) config('app.key'));
        $identifier = (string) ($payload['kid'] ?? '');
        $credential = $this->credentials->findActiveByIdentifier($identifier);

        if ($credential === null) {
            throw new GatewayAuthenticationException('The JWT key identifier is invalid.');
        }

        $client = $credential->client;
        $tenant = $client->tenant;

        return new AuthenticatedGatewayClientData(
            authMethod: GatewayAuthMethod::Jwt,
            tenant: $tenant,
            client: $client,
            credential: $credential,
            legacyErp: null,
            organizationId: (string) ($payload['organization_id'] ?? $client->organization_id ?? $tenant->organization_id),
            clientName: $client->name,
            clientKey: $identifier,
            scopes: array_values($payload['scopes'] ?? $this->scopesForClient($client->getKey())),
            capabilities: array_values($client->enabled_capabilities ?? []),
            providers: array_values($client->enabled_providers ?? []),
            models: array_values($client->enabled_models ?? []),
        );
    }

    /**
     * @return list<string>
     */
    private function scopesForClient(string $clientId): array
    {
        return \App\Domains\Intelligence\Security\Models\GatewayScope::query()
            ->where('gateway_client_id', $clientId)
            ->where('status', 'active')
            ->pluck('scope')
            ->values()
            ->all();
    }
}
