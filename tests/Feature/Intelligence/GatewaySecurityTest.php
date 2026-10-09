<?php

declare(strict_types=1);

namespace Tests\Feature\Intelligence;

use App\Domains\Intelligence\Gateway\Models\GatewayRequest;
use App\Domains\Intelligence\Security\Enums\GatewayAuthMethod;
use App\Domains\Intelligence\Security\Models\GatewayClient;
use App\Domains\Intelligence\Security\Models\GatewayScope;
use App\Domains\Intelligence\Security\Models\GatewayTenant;
use App\Domains\Intelligence\Security\Services\GatewayClientProvisioningService;
use Database\Seeders\IntelligenceRuntimeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class GatewaySecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_key_admin_client_can_manage_gateway_clients(): void
    {
        [$organizationId, $tenant, $adminClient, $apiKey] = $this->provisionGatewayClient(
            scopes: ['admin', 'metrics'],
            capabilities: ['admin', 'metrics']
        );

        $response = $this->withToken($apiKey)->getJson(route('api.gateway.clients.index'));

        $response
            ->assertOk()
            ->assertJsonPath('data.0.id', $adminClient->getKey());
    }

    public function test_gateway_request_history_is_restricted_to_owning_client(): void
    {
        [$organizationId, $tenant, $client, $apiKey] = $this->provisionGatewayClient(
            scopes: ['chat'], capabilities: ['chat']
        );

        $own = GatewayRequest::query()->create([
            'organization_id' => $organizationId,
            'gateway_tenant_id' => $tenant->getKey(),
            'gateway_client_id' => $client->getKey(),
            'auth_method' => 'api_key',
            'capability' => 'chat',
            'status' => 'completed',
            'correlation_id' => 'history-own',
        ]);

        $foreign = GatewayRequest::query()->create([
            'organization_id' => (string) Str::uuid(),
            'gateway_tenant_id' => (string) Str::uuid(),
            'gateway_client_id' => (string) Str::uuid(),
            'auth_method' => 'api_key',
            'capability' => 'chat',
            'status' => 'completed',
            'correlation_id' => 'history-foreign',
        ]);

        $this->withToken($apiKey)
            ->getJson(route('api.gateway.requests.show', ['gatewayRequest' => $own->getKey()]))
            ->assertOk()
            ->assertJsonPath('request.id', $own->getKey());

        $this->withToken($apiKey)
            ->getJson(route('api.gateway.requests.show', ['gatewayRequest' => $foreign->getKey()]))
            ->assertNotFound();
    }

    public function test_gateway_request_history_is_restricted_within_same_tenant(): void
    {
        [$organizationId, $tenant, $client, $apiKey] = $this->provisionGatewayClient(
            scopes: ['chat'], capabilities: ['chat']
        );

        $otherClient = GatewayClient::query()->create([
            'id' => (string) Str::uuid(),
            'gateway_tenant_id' => $tenant->getKey(),
            'organization_id' => $organizationId,
            'client_type' => 'erp',
            'name' => 'Other ERP',
            'status' => 'active',
            'environment' => 'production',
            'enabled_capabilities' => ['chat'],
            'enabled_providers' => ['ollama'],
            'enabled_models' => ['llama3.2:3b'],
            'metadata' => [],
        ]);

        $foreign = GatewayRequest::query()->create([
            'organization_id' => $organizationId,
            'gateway_tenant_id' => $tenant->getKey(),
            'gateway_client_id' => $otherClient->getKey(),
            'auth_method' => 'api_key',
            'capability' => 'chat',
            'status' => 'completed',
            'correlation_id' => 'history-other-client',
        ]);

        $this->withToken($apiKey)
            ->getJson(route('api.gateway.requests.show', ['gatewayRequest' => $foreign->getKey()]))
            ->assertNotFound();
    }

    public function test_hmac_requests_are_authenticated_and_replay_attacks_are_blocked(): void
    {
        $this->seed(IntelligenceRuntimeSeeder::class);

        [$organizationId, $tenant, $client, $apiKey, $apiSecret] = $this->provisionGatewayClient(
            scopes: ['chat'],
            capabilities: ['chat']
        );

        Http::fake([
            'http://localhost:11434/api/chat' => Http::response([
                'model' => 'llama3.2:3b',
                'message' => [
                    'role' => 'assistant',
                    'content' => 'HMAC gateway response.',
                ],
                'done' => true,
                'done_reason' => 'stop',
                'total_duration' => 150000000,
                'prompt_eval_count' => 8,
                'eval_count' => 5,
            ]),
        ]);

        $payload = [
            'organization_id' => $organizationId,
            'erp_system' => 'mobile-app',
            'correlation_id' => 'hmac-corr-001',
            'actor' => ['id' => 'operator-1'],
            'prompt' => 'Summarise the secure request.',
        ];

        $headers = $this->hmacHeaders(
            route('api.gateway.chat', absolute: false),
            $payload,
            $client->credentials()->firstOrFail()->key_identifier,
            $apiSecret,
            (string) now()->timestamp,
            'nonce-001'
        );

        $this->withHeaders($headers)
            ->postJson(route('api.gateway.chat'), $payload)
            ->assertOk()
            ->assertJsonPath('output.text', 'HMAC gateway response.');

        $this->withHeaders($headers)
            ->postJson(route('api.gateway.chat'), $payload)
            ->assertStatus(409);
    }

    public function test_jwt_admin_client_can_open_security_event_feed(): void
    {
        [$organizationId, $tenant, $client] = $this->provisionGatewayClient(
            scopes: ['admin'],
            capabilities: ['admin']
        );

        $credential = $client->credentials()->firstOrFail();
        $jwt = $this->jwtToken($credential->key_identifier, $organizationId, ['admin']);

        $this->withToken($jwt)
            ->getJson(route('api.gateway.security.events'))
            ->assertOk()
            ->assertJsonStructure(['data']);
    }

    public function test_gateway_rejects_cross_tenant_requests_for_api_key_clients(): void
    {
        $this->seed(IntelligenceRuntimeSeeder::class);

        [$organizationId, $tenant, $client, $apiKey] = $this->provisionGatewayClient(
            scopes: ['chat'],
            capabilities: ['chat']
        );

        Http::fake([
            'http://localhost:11434/api/chat' => Http::response([
                'model' => 'llama3.2:3b',
                'message' => ['role' => 'assistant', 'content' => 'Should not be used.'],
                'done' => true,
                'done_reason' => 'stop',
                'total_duration' => 100000000,
                'prompt_eval_count' => 5,
                'eval_count' => 4,
            ]),
        ]);

        $this->withToken($apiKey)
            ->postJson(route('api.gateway.chat'), [
                'organization_id' => (string) Str::uuid(),
                'erp_system' => 'mobile-app',
                'correlation_id' => 'tenant-cross-001',
                'actor' => ['id' => 'user-1'],
                'prompt' => 'This should fail.',
            ])
            ->assertForbidden();
    }

    /**
     * @return array{0: string, 1: GatewayTenant, 2: GatewayClient, 3: string, 4: string}
     */
    private function provisionGatewayClient(array $scopes = [], array $capabilities = []): array
    {
        $organizationId = (string) Str::uuid();

        DB::table('organizations')->insert([
            'id' => $organizationId,
            'name' => 'Gateway Org',
            'slug' => 'gateway-org-'.Str::lower(Str::random(6)),
            'code' => 'GW-'.Str::upper(Str::random(6)),
            'status' => 'active',
            'settings' => json_encode([], JSON_UNESCAPED_SLASHES),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $tenant = GatewayTenant::query()->create([
            'id' => (string) Str::uuid(),
            'organization_id' => $organizationId,
            'name' => 'Tenant A',
            'slug' => 'tenant-a-'.Str::lower(Str::random(6)),
            'status' => 'active',
            'settings' => [],
            'metadata' => [],
        ]);

        $client = GatewayClient::query()->create([
            'id' => (string) Str::uuid(),
            'gateway_tenant_id' => $tenant->getKey(),
            'organization_id' => $organizationId,
            'client_type' => 'mobile_app',
            'name' => 'Mobile App',
            'description' => 'Phase 13 test client',
            'status' => 'active',
            'environment' => 'production',
            'enabled_capabilities' => $capabilities,
            'enabled_providers' => ['ollama'],
            'enabled_models' => ['llama3.2:3b'],
            'metadata' => [],
        ]);

        foreach ($scopes as $scope) {
            GatewayScope::query()->create([
                'id' => (string) Str::uuid(),
                'gateway_tenant_id' => $tenant->getKey(),
                'gateway_client_id' => $client->getKey(),
                'scope' => $scope,
                'status' => 'active',
            ]);
        }

        $material = app(GatewayClientProvisioningService::class)->issueKey($client, GatewayAuthMethod::ApiKey);

        return [$organizationId, $tenant, $client->refresh(), $material->apiKey, $material->apiSecret];
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, string>
     */
    private function hmacHeaders(string $path, array $payload, string $keyIdentifier, string $secret, string $timestamp, string $nonce): array
    {
        $content = json_encode($payload, JSON_UNESCAPED_SLASHES) ?: '{}';
        $signature = hash_hmac('sha256', implode('|', [
            $timestamp,
            $nonce,
            'POST',
            parse_url($path, PHP_URL_PATH) ?: $path,
            hash('sha256', $content),
        ]), $secret);

        return [
            'X-Gateway-Key-Id' => $keyIdentifier,
            'X-Gateway-Timestamp' => $timestamp,
            'X-Gateway-Nonce' => $nonce,
            'X-Gateway-Signature' => $signature,
        ];
    }

    /**
     * @param list<string> $scopes
     */
    private function jwtToken(string $keyIdentifier, string $organizationId, array $scopes): string
    {
        $header = $this->base64UrlEncode(json_encode(['alg' => 'HS256', 'typ' => 'JWT'], JSON_UNESCAPED_SLASHES) ?: '{}');
        $payload = $this->base64UrlEncode(json_encode([
            'kid' => $keyIdentifier,
            'organization_id' => $organizationId,
            'scopes' => $scopes,
            'exp' => now()->addMinutes(10)->timestamp,
        ], JSON_UNESCAPED_SLASHES) ?: '{}');
        $signature = $this->base64UrlEncode(hash_hmac('sha256', $header.'.'.$payload, (string) config('app.key'), true));

        return $header.'.'.$payload.'.'.$signature;
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
