<?php

use App\Domains\Intelligence\Gateway\Services\GatewayHealthService;
use App\Domains\Intelligence\Security\Enums\GatewayAuthMethod;
use App\Domains\Intelligence\Security\Models\GatewayClient;
use App\Domains\Intelligence\Security\Models\GatewayTenant;
use App\Domains\Intelligence\Security\Services\GatewayClientProvisioningService;
use App\Jobs\KnowledgeCleanupJob;
use App\Jobs\KnowledgeHealthJob;
use App\Jobs\KnowledgeVerificationJob;
use App\Jobs\LearningCycleJob;
use App\Jobs\MemoryConsolidationJob;
use App\Jobs\ReindexKnowledgeJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Str;

Artisan::command('inspire', static function () {
    echo Inspiring::quote().PHP_EOL;
})->purpose('Display an inspiring quote');

Artisan::command('gateway:readiness', function (): int {
    $status = app(GatewayHealthService::class)->status();

    echo json_encode($status, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;

    return (bool) data_get($status, 'readiness.ready', false) ? 0 : 1;
})->purpose('Validate that the Enterprise AI Gateway can serve production local-AI traffic');

Artisan::command('gateway:install
    {--check : Inspect installation prerequisites without changing records}
    {--provision : Provision the ERP integration for an existing organization}
    {--verify : Run the live provider and model readiness check}
    {--organization-id= : Existing organization UUID for provisioning}
    {--erp-name= : Name of the ERP application to connect}', function (): int {
    $modes = array_filter(['check' => $this->option('check'), 'provision' => $this->option('provision'), 'verify' => $this->option('verify')]);
    if (count($modes) !== 1) {
        $this->error('Choose exactly one: --check, --provision or --verify.');

        return 2;
    }

    $mode = array_key_first($modes);
    if ($mode === 'check') {
        $checks = [
            'app_key_configured' => filled(config('app.key')),
            'dedicated_deployment' => config('deployment.mode') === 'dedicated',
            'public_registration_disabled' => ! config('deployment.public_registration'),
            'database_available' => false,
            'ollama_configured' => filled(config('intelligence.providers.ollama.endpoint')),
        ];

        try {
            DB::connection()->getPdo();
            $checks['database_available'] = true;
        } catch (Throwable) {
            // Do not expose connection or secret material in installation output.
        }

        $this->line(json_encode(['checks' => $checks], JSON_PRETTY_PRINT));

        return in_array(false, $checks, true) ? 1 : 0;
    }

    if ($mode === 'verify') {
        $status = app(GatewayHealthService::class)->status();
        $this->line(json_encode($status, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return (bool) data_get($status, 'readiness.ready', false) ? 0 : 1;
    }

    $organizationId = (string) $this->option('organization-id');
    $erpName = trim((string) $this->option('erp-name'));
    if (! Str::isUuid($organizationId) || $erpName === '' || ! DB::table('organizations')->where('id', $organizationId)->exists()) {
        $this->error('Provisioning requires an existing --organization-id UUID and a non-empty --erp-name.');

        return 2;
    }

    // Operator-created clients may belong to a tenant with a different slug.
    // Resolve the approved organization/client before creating any installation records.
    $matchingClients = GatewayClient::query()->where('organization_id', $organizationId)->where('name', $erpName)->get();
    $existingClients = $matchingClients->where('status', 'active');
    if ($existingClients->isEmpty() && $matchingClients->isNotEmpty()) {
        $this->error('An inactive ERP client already exists. Resolve it in the operator console before provisioning.');

        return 2;
    }
    if ($existingClients->count() > 1) {
        $this->error('Multiple matching ERP clients exist. Resolve them in the operator console before provisioning.');

        return 2;
    }
    if ($existingClients->isNotEmpty()) {
        $client = $existingClients->first();
        if ($client->tenant?->status !== 'active') {
            $this->error('The existing ERP client tenant is inactive. Resolve it in the operator console.');

            return 2;
        }
        $this->line(json_encode(['tenant_id' => $client->gateway_tenant_id, 'client_id' => $client->getKey(), 'created' => false], JSON_PRETTY_PRINT));
        $this->warn('Existing ERP client reused. Use the operator console to rotate or reissue credentials.');

        return 0;
    }
    $tenant = GatewayTenant::query()->firstOrCreate(
        ['organization_id' => $organizationId, 'slug' => 'dedicated-'.substr($organizationId, 0, 8)],
        ['name' => 'Dedicated ERP Gateway', 'status' => 'active', 'settings' => [], 'metadata' => ['managed_by' => 'VMT']],
    );

    $client = GatewayClient::query()->where('gateway_tenant_id', $tenant->getKey())->where('name', $erpName)->first();
    if ($client !== null) {
        $this->line(json_encode(['tenant_id' => $tenant->getKey(), 'client_id' => $client->getKey(), 'created' => false], JSON_PRETTY_PRINT));
        $this->warn('Existing ERP client reused. Use the operator console to rotate or reissue credentials.');

        return 0;
    }

    $service = app(GatewayClientProvisioningService::class);
    $client = $service->createClient([
        'gateway_tenant_id' => $tenant->getKey(),
        'organization_id' => $organizationId,
        'name' => $erpName,
        'client_type' => 'erp',
        'environment' => app()->environment('production') ? 'production' : 'development',
        'enabled_providers' => [(string) config('gateway.default_provider', 'ollama')],
        'enabled_models' => [(string) config('intelligence.default_model')],
        'enabled_capabilities' => ['chat', 'summarise', 'report', 'classify', 'search'],
        'scopes' => ['chat', 'summarise', 'report', 'classify', 'search'],
        'metadata' => ['provisioned_by' => 'gateway:install'],
    ]);
    $key = $service->issueKey($client, GatewayAuthMethod::ApiKey);
    $this->warn('One-time credentials: redirect output to an access-restricted destination; do not copy to logs.');
    $this->line(json_encode([
        'tenant_id' => $tenant->getKey(), 'client_id' => $client->getKey(),
        'created' => true, 'api_key' => $key->apiKey, 'api_secret' => $key->apiSecret,
    ], JSON_PRETTY_PRINT));

    return 0;
})->purpose('Check, provision and verify a dedicated VMT ERP Gateway deployment');

Schedule::job(new KnowledgeCleanupJob)->hourly();
Schedule::job(new KnowledgeHealthJob)->everyTwoHours();
Schedule::job(new LearningCycleJob)->everyThreeHours();
Schedule::job(new MemoryConsolidationJob)->daily();
Schedule::job(new ReindexKnowledgeJob)->dailyAt('01:00');
Schedule::job(new KnowledgeVerificationJob)->dailyAt('02:00');
