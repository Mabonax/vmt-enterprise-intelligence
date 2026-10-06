<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Gateway\Services;

use App\Domains\Intelligence\Contracts\AiProvider;
use App\Domains\Intelligence\DTOs\ChatMessage;
use App\Domains\Intelligence\DTOs\ChatRequest;
use App\Domains\Intelligence\DTOs\ToolContext;
use App\Domains\Intelligence\Enums\ChatRole;
use App\Domains\Intelligence\Enums\ModelCapability;
use App\Domains\Intelligence\Enums\ProviderType;
use App\Domains\Intelligence\Gateway\DTOs\GatewayCapabilityRequestData;
use App\Domains\Intelligence\Gateway\Models\AiProviderProfile;
use App\Domains\Intelligence\Gateway\Models\GatewayRequest;
use App\Domains\Intelligence\Knowledge\Services\KnowledgeRetrievalService;
use App\Domains\Intelligence\Models\ExecutionPlan;
use App\Domains\Intelligence\Models\ExecutionTrace;
use App\Domains\Intelligence\Security\DTOs\AuthenticatedGatewayClientData;
use App\Domains\Intelligence\Security\Services\GatewayAuthorizationService;
use App\Domains\Intelligence\Security\Services\GatewaySecurityEventLogger;
use App\Domains\Intelligence\Security\Services\GatewayUsageEnforcementService;
use App\Domains\Intelligence\Services\ContextAssembler;
use App\Domains\Intelligence\Services\ModelRouter;
use App\Domains\Intelligence\Services\PromptAssembler;
use App\Domains\Intelligence\Services\ProviderManager;
use App\Domains\Intelligence\Services\ToolExecutor;
use App\Domains\Intelligence\Services\ToolRegistry;
use App\Domains\Intelligence\Services\UsageTrackingService;
use App\Domains\Intelligence\Services\VerificationEngine;
use Illuminate\Http\Request;

class ErpGatewayService
{
    public function __construct(
        private readonly GatewayPermissionContextService $permissions,
        private readonly ProviderAllowlistService $allowlist,
        private readonly GatewayAuditLogger $audit,
        private readonly GatewayAuthorizationService $authorization,
        private readonly GatewayUsageEnforcementService $usageEnforcement,
        private readonly GatewaySecurityEventLogger $events,
        private readonly ContextAssembler $contextAssembler,
        private readonly KnowledgeRetrievalService $knowledge,
        private readonly PromptAssembler $prompts,
        private readonly ModelRouter $router,
        private readonly ProviderManager $providers,
        private readonly ToolRegistry $tools,
        private readonly ToolExecutor $toolExecutor,
        private readonly VerificationEngine $verification,
        private readonly UsageTrackingService $usage,
    ) {}

    public function handle(AuthenticatedGatewayClientData $context, GatewayCapabilityRequestData $request, Request $httpRequest): array
    {
        $this->permissions->authorize($context, $request);
        $this->usageEnforcement->enforce($context);

        $knowledge = $this->knowledge->retrieveForPrompt(
            prompt: $request->prompt,
            filters: [
                'organization_id' => $request->organizationId,
                'workspace' => 'intelligence',
            ],
            limit: (int) config('intelligence.knowledge.retrieval.default_limit', 5),
        );

        $resolvedTools = $this->toolDefinitions();
        $requiredTools = $request->allowActions()
            ? array_values(array_filter(array_map(static fn (array $action): ?string => $action['tool'] ?? null, $request->actions)))
            : [];

        $gatewayRequest = GatewayRequest::query()->create([
            'organization_id' => $request->organizationId,
            'gateway_tenant_id' => $context->tenantId(),
            'gateway_client_id' => $context->clientId(),
            'connected_erp_id' => $context->legacyErp?->getKey(),
            'auth_method' => $context->authMethod->value,
            'capability' => $request->capability,
            'status' => 'running',
            'correlation_id' => (string) $httpRequest->attributes->get('gateway.correlation_id', $request->correlationId),
            'request_payload' => [
                'actor' => $request->actor->toArray(),
                'subject' => $request->subject?->toArray(),
                'prompt' => $request->prompt,
                'context' => $request->context,
                'knowledge_references' => $request->knowledgeReferences,
                'options' => $request->options,
                'actions' => $request->actions,
            ],
            'tool_payload' => [
                'resolved' => array_column($resolvedTools, 'name'),
                'requested' => $requiredTools,
            ],
            'scopes' => $context->scopes,
            'ip_address' => $httpRequest->ip(),
            'metadata' => [
                'client_key' => $context->clientKey,
                'organization_id' => $request->organizationId,
            ],
        ]);

        $this->audit->logConnection($context, 'accepted', $gatewayRequest->correlation_id, ['capability' => $request->capability], 202);
        $this->audit->record($context, $gatewayRequest, 'gateway.request.accepted', [
            'capability' => $request->capability,
            'correlation_id' => $gatewayRequest->correlation_id,
        ]);
        $this->events->log('gateway.request.accepted', $httpRequest, $context, 202, null, ['gateway_request_id' => $gatewayRequest->getKey()]);

        $manualContext = array_merge(
            $this->permissions->contextPayload($context, $request),
            ['knowledge' => $knowledge],
        );

        $promptContext = $this->contextAssembler->assemble(
            systemPrompt: 'You are the VMT Enterprise AI Gateway. Stay provider-agnostic, permission-aware, and evidence-conscious.',
            manualContext: $manualContext,
            pinnedContext: [
                'gateway_request_id' => $gatewayRequest->getKey(),
                'correlation_id' => $gatewayRequest->correlation_id,
                'capability' => $request->capability,
            ],
            toolDefinitions: $request->allowActions() ? $resolvedTools : [],
            userPrompt: $request->prompt,
            user: null,
        );

        $route = $this->router->resolve(
            ModelCapability::Chat,
            preferredProvider: filled($request->options['provider'] ?? null) ? (string) $request->options['provider'] : null,
            preferredModel: filled($request->options['model'] ?? null) ? (string) $request->options['model'] : null,
        );
        $this->allowlist->assertAllowed((string) $route['provider']);
        $this->authorization->assertProviderAllowed($context, (string) $route['provider'], $request->capability, (string) $route['model']);

        $plan = ExecutionPlan::query()->create([
            'conversation_id' => null,
            'agent_id' => null,
            'objective' => $request->prompt,
            'status' => 'running',
            'estimated_complexity' => 'standard',
            'completion_state' => 'open',
            'max_iterations' => 1,
            'required_tools' => $requiredTools,
            'dependencies' => [],
            'retry_strategy' => ['provider_fallback' => $route['fallback_provider'] ?? null],
            'metadata' => [
                'gateway_request_id' => $gatewayRequest->getKey(),
                'capability' => $request->capability,
            ],
        ]);

        $trace = ExecutionTrace::query()->create([
            'conversation_id' => null,
            'agent_id' => null,
            'execution_plan_id' => $plan->getKey(),
            'provider' => (string) $route['provider'],
            'model' => (string) $route['model'],
            'status' => 'running',
            'iterations' => 0,
            'plan_payload' => [
                'required_tools' => $requiredTools,
                'capability' => $request->capability,
            ],
            'memory_payload' => [],
            'trace_payload' => [
                'gateway_request_id' => $gatewayRequest->getKey(),
                'knowledge_summary' => $knowledge['summary'] ?? null,
            ],
            'delegation_payload' => [],
            'metadata' => [
                'organization_id' => $request->organizationId,
                'client_key' => $context->clientKey,
                'correlation_id' => $gatewayRequest->correlation_id,
            ],
        ]);

        $stepPayloads = [[
            'status' => 'completed',
            'step' => 'gateway_pipeline',
            'authorization_status' => 'authorized',
            'knowledge_results' => count($knowledge['results'] ?? []),
            'resolved_tools' => array_column($resolvedTools, 'name'),
        ]];
        $toolResults = [];

        if ($request->allowActions()) {
            foreach ($request->actions as $action) {
                $toolResults[] = $this->toolExecutor->execute(
                    slug: (string) $action['tool'],
                    payload: is_array($action['payload'] ?? null) ? $action['payload'] : [],
                    context: new ToolContext(
                        user: null,
                        conversation: null,
                        agent: null,
                        memory: [],
                        metadata: [
                            'workspace' => 'gateway',
                            'organization_id' => $request->organizationId,
                            'client_key' => $context->clientKey,
                        ],
                    ),
                    trace: $trace,
                );
            }
        }

        [$provider, $activeProvider, $activeModel] = $this->resolveProviderWithFallback($route);
        $chatResponse = $provider->chat(new ChatRequest(
            provider: ProviderType::tryFrom($activeProvider) ?? ProviderType::Ollama,
            model: $activeModel,
            messages: array_map(
                static fn (array $message): ChatMessage => new ChatMessage(
                    role: ChatRole::from($message['role']),
                    content: $message['content'],
                ),
                $this->prompts->build($promptContext),
            ),
            metadata: [
                'tools' => $request->allowActions() ? $resolvedTools : [],
                'gateway_request_id' => $gatewayRequest->getKey(),
            ],
        ));

        $verification = $this->verification->verify($trace, $stepPayloads, $toolResults);
        $providerProfile = AiProviderProfile::query()->firstWhere('provider_key', $activeProvider);

        $gatewayRequest->forceFill([
            'provider_profile_id' => $providerProfile?->getKey(),
            'status' => $verification->passed ? 'completed' : 'needs_input',
            'provider' => $activeProvider,
            'model' => $activeModel,
            'verification_passed' => $verification->passed,
            'input_tokens' => $chatResponse->usage->inputTokens,
            'output_tokens' => $chatResponse->usage->outputTokens,
            'latency_ms' => $chatResponse->usage->latencyMs,
            'cost' => $chatResponse->usage->cost,
            'response_payload' => [
                'text' => $chatResponse->message->content,
                'tool_calls' => array_map(
                    static fn ($toolCall): array => method_exists($toolCall, 'toArray') ? $toolCall->toArray() : [],
                    $chatResponse->toolCalls,
                ),
            ],
            'verification_payload' => [
                'passed' => $verification->passed,
                'confidence_score' => $verification->confidenceScore,
                'checks' => $verification->checks,
                'missing_information' => $verification->missingInformation,
            ],
            'completed_at' => now(),
            'metadata' => array_merge($gatewayRequest->metadata ?? [], [
                'knowledge_summary' => $knowledge['summary'] ?? null,
                'tool_count' => count($toolResults),
                'trace_id' => $trace->getKey(),
            ]),
        ])->save();

        $trace->forceFill([
            'status' => $verification->passed ? 'completed' : 'needs_input',
            'completion_reason' => $verification->passed ? 'verified' : 'follow_up_required',
            'iterations' => 1,
            'duration_ms' => $chatResponse->usage->latencyMs,
            'input_tokens' => $chatResponse->usage->inputTokens,
            'output_tokens' => $chatResponse->usage->outputTokens,
            'step_payloads' => $stepPayloads,
            'verification_payload' => [
                'passed' => $verification->passed,
                'confidence_score' => $verification->confidenceScore,
            ],
            'trace_payload' => array_merge($trace->trace_payload ?? [], [
                'gateway_request_id' => $gatewayRequest->getKey(),
                'provider' => $activeProvider,
                'model' => $activeModel,
            ]),
        ])->save();

        $plan->forceFill([
            'status' => $trace->status,
            'completion_state' => $verification->passed ? 'fulfilled' : 'needs_input',
            'attempts' => 1,
        ])->save();

        $this->usage->track(
            provider: $activeProvider,
            model: $activeModel,
            usage: $chatResponse->usage,
            conversation: null,
            message: null,
            metadata: [
                'gateway_request_id' => $gatewayRequest->getKey(),
                'organization_id' => $request->organizationId,
                'correlation_id' => $gatewayRequest->correlation_id,
            ],
        );

        $this->audit->record($context, $gatewayRequest, 'gateway.request.completed', [
            'status' => $gatewayRequest->status,
            'provider' => $activeProvider,
            'model' => $activeModel,
            'verification_passed' => $verification->passed,
        ]);
        $this->audit->logConnection(
            $context,
            $gatewayRequest->status,
            $gatewayRequest->correlation_id,
            ['gateway_request_id' => $gatewayRequest->getKey()],
            200,
        );
        $this->usageEnforcement->record($context, $gatewayRequest->refresh(), ['verification' => ['passed' => $verification->passed]]);
        $this->events->log('gateway.request.completed', $httpRequest, $context, 200, null, [
            'gateway_request_id' => $gatewayRequest->getKey(),
            'provider' => $activeProvider,
            'model' => $activeModel,
        ]);

        return [
            'request' => $gatewayRequest->refresh(),
            'trace' => $trace->refresh(),
            'provider' => [
                'key' => $activeProvider,
                'model' => $activeModel,
            ],
            'output' => [
                'text' => $chatResponse->message->content,
            ],
            'citations' => array_map(static fn (array $result): array => [
                'id' => $result['id'] ?? null,
                'title' => $result['title'] ?? null,
                'score' => $result['score'] ?? null,
            ], $knowledge['results'] ?? []),
            'verification' => [
                'passed' => $verification->passed,
                'confidence_score' => $verification->confidenceScore,
                'missing_information' => $verification->missingInformation,
            ],
            'tools' => [
                'resolved' => array_column($resolvedTools, 'name'),
                'executed' => array_map(static fn ($result): array => [
                    'tool' => $result->tool,
                    'success' => $result->success,
                    'output' => $result->output,
                ], $toolResults),
            ],
        ];
    }

    /**
     * @return list<array{name: string, description: string, schema: array<string, mixed>}>
     */
    private function toolDefinitions(): array
    {
        return collect($this->tools->definitions())
            ->map(static fn ($tool): array => [
                'name' => $tool->slug,
                'description' => $tool->description ?? '',
                'schema' => $tool->input_schema ?? [],
            ])
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $route
     * @return array{0: AiProvider, 1: string, 2: string}
     */
    private function resolveProviderWithFallback(array $route): array
    {
        $primaryProvider = (string) $route['provider'];
        $primaryModel = (string) $route['model'];

        try {
            return [$this->providers->resolve($primaryProvider), $primaryProvider, $primaryModel];
        } catch (\Throwable) {
            $fallbackProvider = (string) ($route['fallback_provider'] ?? config('intelligence.model_routing.fallback.provider'));
            $fallbackModel = (string) ($route['fallback_model'] ?? config('intelligence.model_routing.fallback.model'));
            $this->allowlist->assertAllowed($fallbackProvider);

            return [$this->providers->resolve($fallbackProvider), $fallbackProvider, $fallbackModel];
        }
    }
}
