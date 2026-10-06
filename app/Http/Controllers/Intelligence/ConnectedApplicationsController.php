<?php

declare(strict_types=1);

namespace App\Http\Controllers\Intelligence;

use App\Domains\Intelligence\Security\Enums\GatewayAuthMethod;
use App\Domains\Intelligence\Security\Models\GatewayClient;
use App\Domains\Intelligence\Security\Models\GatewayTenant;
use App\Domains\Intelligence\Security\Services\GatewayClientProvisioningService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ConnectedApplicationsController extends Controller
{
    public function __construct(
        private readonly GatewayClientProvisioningService $provisioning,
    ) {}

    public function index(Request $request): Response
    {
        $this->authorizeOperator($request);

        $tenants = GatewayTenant::query()
            ->withCount('clients')
            ->latest()
            ->get()
            ->map(fn (GatewayTenant $tenant): array => [
                'id' => $tenant->getKey(),
                'organization_id' => $tenant->organization_id,
                'name' => $tenant->name,
                'slug' => $tenant->slug,
                'status' => $tenant->status,
                'clients_count' => $tenant->clients_count,
            ])
            ->values();

        $clients = GatewayClient::query()
            ->with(['tenant', 'credentials' => fn ($query) => $query->latest()])
            ->latest()
            ->get()
            ->map(fn (GatewayClient $client): array => [
                'id' => $client->getKey(),
                'tenant' => $client->tenant?->name,
                'tenant_id' => $client->gateway_tenant_id,
                'name' => $client->name,
                'type' => $client->client_type,
                'environment' => $client->environment,
                'status' => $client->status,
                'providers' => $client->enabled_providers ?? [],
                'models' => $client->enabled_models ?? [],
                'capabilities' => $client->enabled_capabilities ?? [],
                'last_used_at' => optional($client->last_used_at)->toIso8601String(),
                'credentials' => $client->credentials->map(fn ($credential): array => [
                    'key_identifier' => $credential->key_identifier,
                    'auth_method' => $credential->auth_method,
                    'status' => $credential->status,
                    'version' => $credential->version,
                    'last_used_at' => optional($credential->last_used_at)->toIso8601String(),
                    'expires_at' => optional($credential->expires_at)->toIso8601String(),
                    'created_at' => optional($credential->created_at)->toIso8601String(),
                ])->values(),
            ])
            ->values();

        return Inertia::render('Intelligence/AdminConsole/ConnectedApplications', [
            'page' => [
                'title' => 'Connected Applications',
                'eyebrow' => 'Enterprise AI Gateway',
                'description' => 'Register ERP clients, constrain their AI capabilities, and manage gateway credentials.',
            ],
            'tenants' => $tenants,
            'clients' => $clients,
            'organizations' => DB::table('organizations')
                ->select(['id', 'name', 'code'])
                ->orderBy('name')
                ->get()
                ->map(fn ($organization): array => [
                    'id' => (string) $organization->id,
                    'name' => (string) $organization->name,
                    'code' => (string) ($organization->code ?? ''),
                ])
                ->values(),
            'defaults' => [
                'provider' => (string) config('intelligence.default_provider'),
                'model' => (string) config('intelligence.default_model'),
                'capabilities' => array_values(array_filter(
                    config('gateway.capabilities', []),
                    static fn (string $capability): bool => ! in_array($capability, ['admin', 'metrics'], true),
                )),
            ],
            'routes' => [
                'storeTenant' => route('intelligence.admin-console.connected-applications.tenants.store'),
                'storeClient' => route('intelligence.admin-console.connected-applications.clients.store'),
                'issueKey' => route('intelligence.admin-console.connected-applications.clients.keys.issue', ['client' => '__CLIENT__']),
                'revokeKey' => route('intelligence.admin-console.connected-applications.clients.keys.revoke', [
                    'client' => '__CLIENT__',
                    'keyIdentifier' => '__KEY__',
                ]),
            ],
        ]);
    }

    public function storeTenant(Request $request): RedirectResponse
    {
        $this->authorizeOperator($request);

        $validated = $request->validate([
            'organization_id' => ['required', 'uuid', 'exists:organizations,id'],
            'name' => ['required', 'string', 'max:191'],
            'slug' => ['nullable', 'string', 'max:191', Rule::unique('gateway_tenants', 'slug')],
        ]);

        GatewayTenant::query()->create([
            'organization_id' => $validated['organization_id'],
            'name' => $validated['name'],
            'slug' => $validated['slug'] ?: Str::slug($validated['name']).'-'.Str::lower(Str::random(6)),
            'status' => 'active',
            'settings' => [],
            'metadata' => [
                'created_from' => 'admin_console',
                'created_by' => (string) $request->user()->getKey(),
            ],
        ]);

        return back()->with('status', 'Gateway tenant created.');
    }

    public function storeClient(Request $request): RedirectResponse
    {
        $this->authorizeOperator($request);

        $validated = $request->validate([
            'gateway_tenant_id' => ['required', 'uuid', 'exists:gateway_tenants,id'],
            'name' => ['required', 'string', 'max:191'],
            'description' => ['nullable', 'string', 'max:2000'],
            'client_type' => ['required', 'string', 'max:100'],
            'environment' => ['required', Rule::in(['development', 'staging', 'production'])],
            'enabled_providers' => ['required', 'array', 'min:1'],
            'enabled_providers.*' => ['string', 'max:100'],
            'enabled_models' => ['required', 'array', 'min:1'],
            'enabled_models.*' => ['string', 'max:191'],
            'enabled_capabilities' => ['required', 'array', 'min:1'],
            'enabled_capabilities.*' => ['string', 'max:100'],
            'scopes' => ['nullable', 'array'],
            'scopes.*' => ['string', 'max:100'],
            'rate_limit_per_minute' => ['nullable', 'integer', 'min:1'],
            'daily_quota' => ['nullable', 'integer', 'min:1'],
        ]);

        $tenant = GatewayTenant::query()->findOrFail($validated['gateway_tenant_id']);

        $client = $this->provisioning->createClient(array_merge($validated, [
            'organization_id' => $tenant->organization_id,
            'status' => 'active',
            'scopes' => array_values($validated['enabled_capabilities']),
            'created_by' => (string) $request->user()->getKey(),
            'updated_by' => (string) $request->user()->getKey(),
            'metadata' => [
                'created_from' => 'admin_console',
            ],
        ]));

        $material = $this->provisioning->issueKey($client, GatewayAuthMethod::ApiKey);

        return back()
            ->with('status', 'Connected application created. Copy the credentials now; the API key and secret are shown only once.')
            ->with('gatewayCredential', [
                'client_id' => $client->getKey(),
                'client_name' => $client->name,
                'key_identifier' => $material->keyIdentifier,
                'api_key' => $material->apiKey,
                'api_secret' => $material->apiSecret,
                'version' => $material->version,
                'expires_at' => $material->expiresAt,
            ]);
    }

    public function issueKey(Request $request, GatewayClient $client): RedirectResponse
    {
        $this->authorizeOperator($request);

        $material = $this->provisioning->issueKey($client, GatewayAuthMethod::ApiKey);

        return back()
            ->with('status', 'New gateway credential issued. Copy it now; secret material is not displayed again.')
            ->with('gatewayCredential', [
                'client_id' => $client->getKey(),
                'client_name' => $client->name,
                'key_identifier' => $material->keyIdentifier,
                'api_key' => $material->apiKey,
                'api_secret' => $material->apiSecret,
                'version' => $material->version,
                'expires_at' => $material->expiresAt,
            ]);
    }

    public function revokeKey(Request $request, GatewayClient $client, string $keyIdentifier): RedirectResponse
    {
        $this->authorizeOperator($request);

        $this->provisioning->revokeKey($client->getKey(), $keyIdentifier);

        return back()->with('status', 'Gateway credential revoked.');
    }

    private function authorizeOperator(Request $request): void
    {
        abort_unless($request->user()?->can('intelligence.manage') ?? false, 403);
    }
}
