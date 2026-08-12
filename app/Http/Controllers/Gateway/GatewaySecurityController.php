<?php

declare(strict_types=1);

namespace App\Http\Controllers\Gateway;

use App\Domains\Intelligence\Security\DTOs\AuthenticatedGatewayClientData;
use App\Domains\Intelligence\Security\Enums\GatewayAuthMethod;
use App\Domains\Intelligence\Security\Exceptions\GatewayAuthorizationException;
use App\Domains\Intelligence\Security\Models\GatewayClient;
use App\Domains\Intelligence\Security\Repositories\GatewayClientRepository;
use App\Domains\Intelligence\Security\Repositories\GatewaySecurityEventRepository;
use App\Domains\Intelligence\Security\Repositories\GatewayTenantRepository;
use App\Domains\Intelligence\Security\Repositories\GatewayUsageRepository;
use App\Domains\Intelligence\Security\Services\GatewayClientProvisioningService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Gateway\GatewayClientManagementRequest;
use App\Http\Requests\Gateway\GatewayKeyLifecycleRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GatewaySecurityController extends Controller
{
    public function __construct(
        private readonly GatewayClientProvisioningService $provisioning,
        private readonly GatewayTenantRepository $tenants,
        private readonly GatewayClientRepository $clients,
        private readonly GatewaySecurityEventRepository $events,
        private readonly GatewayUsageRepository $usage,
    ) {}

    public function tenants(): JsonResponse
    {
        $this->ensureAdmin(request());

        return response()->json(['data' => $this->tenants->latest()->values()]);
    }

    public function clients(): JsonResponse
    {
        $this->ensureAdmin(request());

        return response()->json(['data' => $this->clients->latest()->values()]);
    }

    public function storeClient(GatewayClientManagementRequest $request): JsonResponse
    {
        $this->ensureAdmin($request);

        $client = $this->provisioning->createClient(array_merge(
            $request->validated(),
            ['created_by' => $this->actor($request), 'updated_by' => $this->actor($request)],
        ));

        return response()->json(['data' => $client->refresh()], 201);
    }

    public function updateClient(GatewayClientManagementRequest $request, GatewayClient $client): JsonResponse
    {
        $this->ensureAdmin($request);

        $client->forceFill(array_merge(
            $request->safe()->except(['gateway_tenant_id', 'policy', 'scopes']),
            ['updated_by' => $this->actor($request)],
        ))->save();

        return response()->json(['data' => $client->refresh()]);
    }

    public function destroyClient(Request $request, GatewayClient $client): JsonResponse
    {
        $this->ensureAdmin($request);

        $client->delete();

        return response()->json(status: 204);
    }

    public function createKey(GatewayKeyLifecycleRequest $request): JsonResponse
    {
        $this->ensureAdmin($request);

        $client = GatewayClient::query()->findOrFail($request->string('client_id')->toString());
        $material = $this->provisioning->issueKey($client, GatewayAuthMethod::ApiKey, $request->date('expires_at'));

        return response()->json(['data' => $material], 201);
    }

    public function rotateKey(GatewayKeyLifecycleRequest $request): JsonResponse
    {
        $this->ensureAdmin($request);

        $client = GatewayClient::query()->findOrFail($request->string('client_id')->toString());
        $material = $this->provisioning->rotateKey($client, $request->string('key_identifier')->toString() ?: null, $request->date('expires_at'));

        return response()->json(['data' => $material]);
    }

    public function revokeKey(GatewayKeyLifecycleRequest $request): JsonResponse
    {
        $this->ensureAdmin($request);

        $revoked = $this->provisioning->revokeKey(
            $request->string('client_id')->toString(),
            $request->string('key_identifier')->toString() ?: null,
        );

        return response()->json(['revoked' => $revoked]);
    }

    public function securityEvents(): JsonResponse
    {
        $this->ensureAdmin(request());

        return response()->json(['data' => $this->events->latest()->values()]);
    }

    public function usage(): JsonResponse
    {
        $this->ensureAdmin(request());

        return response()->json(['data' => $this->usage->latest()->values()]);
    }

    private function ensureAdmin(Request $request): void
    {
        /** @var AuthenticatedGatewayClientData|null $context */
        $context = $request->attributes->get('gateway.auth');
        $scopes = $context?->scopes ?? [];
        $capabilities = $context?->capabilities ?? [];

        if (! in_array('admin', $scopes, true) && ! in_array('admin', $capabilities, true)) {
            throw new GatewayAuthorizationException('The gateway client lacks administrative scope.');
        }
    }

    private function actor(Request $request): string
    {
        /** @var AuthenticatedGatewayClientData|null $context */
        $context = $request->attributes->get('gateway.auth');

        return $context?->clientName ?? 'gateway';
    }
}
