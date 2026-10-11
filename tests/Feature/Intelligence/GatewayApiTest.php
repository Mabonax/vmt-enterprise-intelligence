<?php

declare(strict_types=1);

namespace Tests\Feature\Intelligence;

use App\Domains\Connections\Models\ConnectedErp;
use App\Domains\Connections\Models\ErpApiKey;
use App\Domains\Intelligence\Gateway\Models\GatewayRequest;
use App\Domains\Intelligence\Knowledge\Services\KnowledgeRetrievalService;
use App\Domains\Intelligence\Models\ExecutionPlan;
use Database\Seeders\IntelligenceRuntimeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class GatewayApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_aggregate_summary_can_explicitly_exclude_knowledge_retrieval(): void
    {
        $this->seed(IntelligenceRuntimeSeeder::class);
        [$organizationId, $erp, $secret] = $this->provisionErp();
        $this->mock(KnowledgeRetrievalService::class)
            ->shouldNotReceive('retrieveForPrompt');
        Http::fake(['*/chat' => Http::response([
            'model' => 'llama3.2:3b', 'message' => ['role' => 'assistant', 'content' => 'Four appointments.'], 'done' => true,
        ])]);
        $this->withHeaders(['X-ERP-System' => $erp->system_key, 'X-ERP-Key' => $secret])
            ->postJson(route('api.gateway.summarise'), [
                'organization_id' => $organizationId, 'erp_system' => $erp->system_key,
                'correlation_id' => 'aggregate-only-001', 'actor' => ['id' => 'synthetic-operator'],
                'prompt' => 'Summarise supplied counts', 'context' => ['total' => 4],
                'options' => ['retrieve_knowledge' => false, 'allow_actions' => false],
            ])->assertOk()->assertJsonPath('verification.passed', true);
        Http::assertSent(fn ($request) => $request['tools'] === []);
    }

    public function test_provider_outage_returns_safe_failure_and_records_failed_trace(): void
    {
        $this->seed(IntelligenceRuntimeSeeder::class);
        [$organizationId, $erp, $secret] = $this->provisionErp();
        Http::fake(['*/chat' => Http::failedConnection()]);
        $this->withHeaders(['X-ERP-System' => $erp->system_key, 'X-ERP-Key' => $secret])
            ->postJson(route('api.gateway.summarise'), [
                'organization_id' => $organizationId, 'erp_system' => $erp->system_key,
                'correlation_id' => 'provider-outage-001', 'actor' => ['id' => 'synthetic-operator'],
                'prompt' => 'Summarise counts', 'options' => ['retrieve_knowledge' => false],
            ])->assertStatus(502)->assertJsonPath('status', 'failed');
        $this->assertDatabaseHas('gateway_requests', ['correlation_id' => 'provider-outage-001', 'status' => 'failed']);
        $this->assertDatabaseHas('execution_traces', ['status' => 'failed', 'completion_reason' => 'provider_error']);
        $this->assertDatabaseHas('audit_entries', ['event' => 'gateway.request.failed']);
    }

    public function test_missing_runtime_model_does_not_expose_provider_error_body(): void
    {
        $this->seed(IntelligenceRuntimeSeeder::class);
        [$organizationId, $erp, $secret] = $this->provisionErp();
        Http::fake(['*/chat' => Http::response(['error' => 'model missing secret-provider-detail'], 404)]);
        $response = $this->withHeaders(['X-ERP-System' => $erp->system_key, 'X-ERP-Key' => $secret])
            ->postJson(route('api.gateway.summarise'), [
                'organization_id' => $organizationId, 'erp_system' => $erp->system_key,
                'correlation_id' => 'model-missing-001', 'actor' => ['id' => 'synthetic-operator'],
                'prompt' => 'Summarise counts', 'options' => ['retrieve_knowledge' => false],
            ])->assertStatus(502);
        $this->assertStringNotContainsString('secret-provider-detail', $response->getContent());
    }

    public function test_long_prompt_preserves_request_and_bounds_mysql_plan_objective(): void
    {
        $this->seed(IntelligenceRuntimeSeeder::class);
        [$organizationId, $erp, $secret] = $this->provisionErp();
        $prompt = str_repeat('Synthetic operational context. ', 30);
        Http::fake(['*/chat' => Http::response([
            'model' => 'llama3.2:3b', 'message' => ['role' => 'assistant', 'content' => 'Summary.'], 'done' => true,
        ])]);
        $this->withHeaders(['X-ERP-System' => $erp->system_key, 'X-ERP-Key' => $secret])
            ->postJson(route('api.gateway.summarise'), [
                'organization_id' => $organizationId, 'erp_system' => $erp->system_key,
                'correlation_id' => 'long-prompt-001', 'actor' => ['id' => 'synthetic-operator'],
                'prompt' => $prompt, 'options' => ['retrieve_knowledge' => false],
            ])->assertOk();
        $plan = ExecutionPlan::query()->firstOrFail();
        $this->assertLessThanOrEqual(255, mb_strlen($plan->objective));
        $request = GatewayRequest::where('correlation_id', 'long-prompt-001')->firstOrFail();
        $this->assertSame(trim($prompt), $request->request_payload['prompt']);
    }

    public function test_gateway_chat_endpoint_executes_the_enterprise_pipeline(): void
    {
        $this->seed(IntelligenceRuntimeSeeder::class);

        [$organizationId, $erp, $secret] = $this->provisionErp();

        Http::fake([
            'http://localhost:11434/api/chat' => Http::response([
                'model' => 'llama3.2:3b',
                'message' => [
                    'role' => 'assistant',
                    'content' => 'Gateway response from Ollama.',
                ],
                'done' => true,
                'done_reason' => 'stop',
                'total_duration' => 500000000,
                'prompt_eval_count' => 12,
                'eval_count' => 8,
            ]),
        ]);

        $response = $this->withHeaders([
            'X-ERP-System' => $erp->system_key,
            'X-ERP-Key' => $secret,
        ])->postJson(route('api.gateway.chat'), [
            'organization_id' => $organizationId,
            'erp_system' => $erp->system_key,
            'correlation_id' => 'corr-001',
            'actor' => [
                'id' => 'user-42',
                'roles' => ['clinician'],
                'permissions' => ['patients.view'],
            ],
            'subject' => [
                'type' => 'patient_record',
                'id' => 'pat-9001',
            ],
            'prompt' => 'Summarise the patient handover note.',
            'context' => [
                'module' => 'clinical',
            ],
            'knowledge_references' => ['clinical-policies'],
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('status', 'completed')
            ->assertJsonPath('provider.key', 'ollama')
            ->assertJsonPath('verification.passed', true)
            ->assertJsonPath('output.text', 'Gateway response from Ollama.');

        Http::assertSent(fn ($request): bool => $request['tools'] === []);
        $this->assertDatabaseHas('gateway_requests', [
            'capability' => 'chat',
            'status' => 'completed',
            'connected_erp_id' => $erp->getKey(),
            'provider' => 'ollama',
        ]);
        $this->assertDatabaseHas('execution_traces', [
            'provider' => 'ollama',
            'status' => 'completed',
        ]);
        $this->assertDatabaseHas('provider_usage', [
            'provider' => 'ollama',
            'model' => 'llama3.2:3b',
        ]);
        $this->assertDatabaseHas('connection_logs', [
            'connected_erp_id' => $erp->getKey(),
            'request_identifier' => 'corr-001',
        ]);
    }

    public function test_gateway_action_endpoint_executes_approved_tools(): void
    {
        $this->seed(IntelligenceRuntimeSeeder::class);

        [$organizationId, $erp, $secret] = $this->provisionErp();

        Http::fake([
            'http://localhost:11434/api/chat' => Http::response([
                'model' => 'llama3.2:3b',
                'message' => [
                    'role' => 'assistant',
                    'content' => 'Action workflow completed.',
                ],
                'done' => true,
                'done_reason' => 'stop',
                'total_duration' => 250000000,
                'prompt_eval_count' => 10,
                'eval_count' => 6,
            ]),
        ]);

        $response = $this->withHeaders([
            'X-ERP-System' => $erp->system_key,
            'X-ERP-Key' => $secret,
        ])->postJson(route('api.gateway.action'), [
            'organization_id' => $organizationId,
            'erp_system' => $erp->system_key,
            'correlation_id' => 'corr-002',
            'actor' => [
                'id' => 'user-42',
            ],
            'prompt' => 'Execute the approved action.',
            'actions' => [
                [
                    'tool' => 'current_datetime',
                    'payload' => [],
                ],
            ],
            'options' => [
                'allow_actions' => true,
            ],
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('status', 'completed')
            ->assertJsonPath('tools.executed.0.tool', 'current_datetime')
            ->assertJsonPath('verification.passed', true);

        $this->assertDatabaseHas('tool_execution_logs', [
            'tool_name' => 'current_datetime',
            'status' => 'completed',
        ]);
    }

    public function test_failed_inference_does_not_leave_gateway_execution_running(): void
    {
        $this->seed(IntelligenceRuntimeSeeder::class);
        [$organizationId, $erp, $secret] = $this->provisionErp();

        Http::fake(['http://localhost:11434/api/chat' => Http::response(['error' => 'offline'], 500)]);

        $response = $this->withHeaders([
            'X-ERP-System' => $erp->system_key,
            'X-ERP-Key' => $secret,
        ])->postJson(route('api.gateway.chat'), [
            'organization_id' => $organizationId,
            'erp_system' => $erp->system_key,
            'correlation_id' => 'corr-provider-failure',
            'actor' => ['id' => 'user-42'],
            'prompt' => 'Summarise this record.',
        ]);

        $this->assertGreaterThanOrEqual(400, $response->status());
        $this->assertDatabaseHas('gateway_requests', [
            'correlation_id' => 'corr-provider-failure',
            'status' => 'failed',
        ]);
        $this->assertDatabaseHas('execution_traces', ['status' => 'failed']);
        $this->assertDatabaseHas('execution_plans', ['status' => 'failed']);
    }

    public function test_gateway_rejects_invalid_credentials(): void
    {
        [$organizationId, $erp] = $this->provisionErp();

        $this->withHeaders([
            'X-ERP-System' => $erp->system_key,
            'X-ERP-Key' => 'invalid-secret',
        ])->postJson(route('api.gateway.chat'), [
            'organization_id' => $organizationId,
            'erp_system' => $erp->system_key,
            'correlation_id' => 'corr-003',
            'actor' => ['id' => 'user-42'],
            'prompt' => 'Hello world',
        ])->assertUnauthorized();
    }

    /**
     * @return array{0: string, 1: ConnectedErp, 2: string}
     */
    private function provisionErp(): array
    {
        $organizationId = (string) Str::uuid();
        DB::table('organizations')->insert([
            'id' => $organizationId,
            'name' => 'Clinic ERP Org',
            'slug' => 'clinic-erp-org',
            'code' => 'CLINIC-ERP',
            'status' => 'active',
            'settings' => json_encode([], JSON_UNESCAPED_SLASHES),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $erp = ConnectedErp::query()->create([
            'id' => (string) Str::uuid(),
            'organization_id' => $organizationId,
            'name' => 'Clinic ERP',
            'system_key' => 'clinic-erp',
            'base_url' => 'http://clinic-erp.local',
            'status' => 'active',
            'allowed_models' => [],
            'permissions' => ['chat', 'summarise', 'report', 'translate', 'classify', 'search', 'action'],
            'rate_limits' => [],
            'metadata' => [],
        ]);

        $secret = 'gateway-shared-secret';

        ErpApiKey::query()->create([
            'id' => (string) Str::uuid(),
            'connected_erp_id' => $erp->getKey(),
            'name' => 'Primary key',
            'key_hash' => hash('sha256', $secret),
            'is_active' => true,
        ]);

        return [$organizationId, $erp, $secret];
    }
}
