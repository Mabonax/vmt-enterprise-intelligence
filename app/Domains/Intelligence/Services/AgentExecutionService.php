<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Services;

use App\Domains\Intelligence\DTOs\ChatMessage;
use App\Domains\Intelligence\DTOs\ChatRequest;
use App\Domains\Intelligence\DTOs\ToolContext;
use App\Domains\Intelligence\DTOs\VerificationResultData;
use App\Domains\Intelligence\Enums\ChatRole;
use App\Domains\Intelligence\Enums\ModelCapability;
use App\Domains\Intelligence\Enums\ProviderType;
use App\Domains\Intelligence\Knowledge\Services\LearningService;
use App\Domains\Intelligence\Models\Agent;
use App\Domains\Intelligence\Models\Conversation;
use App\Domains\Intelligence\Models\ExecutionPlan;
use App\Domains\Intelligence\Models\ExecutionPlanStep;
use App\Domains\Intelligence\Models\ExecutionTrace;
use App\Models\User;
use Throwable;

class AgentExecutionService
{
    public function __construct(
        private readonly AgentResolver $agentResolver,
        private readonly ContextAssembler $contextAssembler,
        private readonly PromptAssembler $promptAssembler,
        private readonly PlanningEngine $planner,
        private readonly ToolExecutor $toolExecutor,
        private readonly ModelRouter $modelRouter,
        private readonly ProviderManager $providers,
        private readonly MemoryRetriever $memoryRetriever,
        private readonly MemoryInjectionService $memoryInjection,
        private readonly VerificationEngine $verification,
        private readonly WorkflowRuntime $workflowRuntime,
        private readonly AgentDelegationService $delegationService,
        private readonly LearningService $learningService,
    ) {}

    public function execute(User $user, Conversation $conversation, string $prompt, ?string $agentId = null): array
    {
        $agent = $this->agentResolver->resolve($conversation, $agentId, $user);
        $memories = $agent?->memory_enabled ? $this->memoryRetriever->retrieveForUser($user, $agent) : collect();
        $memorySummary = $this->memoryInjection->inject($memories);
        $history = $conversation->messages()->latest()->limit(5)->get()->reverse()->map(
            static fn ($message): ChatMessage => new ChatMessage(
                role: ChatRole::from($message->role),
                content: $message->content,
            )
        )->values()->all();
        $allowedTools = $this->resolveAllowedTools($agent);

        $context = $this->contextAssembler->assemble(
            systemPrompt: $agent?->system_instructions ?: 'You are the enterprise intelligence runtime.',
            conversationHistory: $history,
            manualContext: $memorySummary !== '' ? ['memory' => $memorySummary] : [],
            pinnedContext: [
                'conversation_id' => $conversation->id,
                'conversation_status' => $conversation->status->value,
                'organization_id' => $user->organization_id,
            ],
            toolDefinitions: $allowedTools,
            userPrompt: $prompt,
            user: $user,
        );

        $planData = $this->planner->plan($prompt, $agent, [
            'memory_count' => $memories->count(),
            'conversation_id' => $conversation->id,
            'organization_id' => $user->organization_id,
        ]);
        $plan = ExecutionPlan::query()->create([
            'conversation_id' => $conversation->id,
            'agent_id' => $agent?->id,
            'objective' => $planData->objective,
            'status' => 'running',
            'estimated_complexity' => $planData->estimatedComplexity,
            'completion_state' => $planData->completionState,
            'max_iterations' => $planData->maxIterations,
            'required_tools' => $planData->requiredTools,
            'dependencies' => $planData->dependencies,
            'retry_strategy' => $planData->retryStrategy,
            'metadata' => $planData->metadata,
        ]);

        $stepPayloads = [];
        $persistedSteps = [];
        foreach ($planData->steps as $stepData) {
            $persistedSteps[] = ExecutionPlanStep::query()->create(array_merge($stepData->toArray(), [
                'execution_plan_id' => $plan->id,
                'status' => 'pending',
            ]));
        }

        $route = $this->modelRouter->resolve(
            ModelCapability::Chat,
            $agent?->default_provider,
            $agent?->default_model,
        );

        $trace = ExecutionTrace::query()->create([
            'conversation_id' => $conversation->id,
            'agent_id' => $agent?->id,
            'execution_plan_id' => $plan->id,
            'provider' => $route['provider'],
            'model' => $route['model'],
            'status' => 'running',
            'iterations' => 0,
            'plan_payload' => [
                'objective' => $planData->objective,
                'required_tools' => $planData->requiredTools,
                'dependencies' => $planData->dependencies,
                'retry_strategy' => $planData->retryStrategy,
                'steps' => array_map(static fn ($step) => $step->toArray(), $planData->steps),
            ],
            'memory_payload' => $memories->map(fn ($memory) => $memory->only(['id', 'content', 'memory_type', 'visibility']))->all(),
            'trace_payload' => [
                'started_at' => now()->toIso8601String(),
                'context' => $context->runtimeContext,
            ],
            'delegation_payload' => [],
        ]);

        $toolResults = [];
        $workflowExecutions = [];
        $delegations = [];

        foreach ($persistedSteps as $step) {
            if (count($stepPayloads) >= $plan->max_iterations) {
                break;
            }

            $stepPayload = $this->executeStep($step, $planData->objective, $user, $conversation, $agent, $trace, $toolResults, $workflowExecutions);
            $stepPayloads[] = $stepPayload;

            if (($stepPayload['delegated'] ?? false) === true) {
                $delegation = $this->delegateIfPossible($trace, $agent, $planData->objective, $stepPayload);

                if ($delegation !== null) {
                    $delegations[] = $delegation->only(['id', 'source_agent_id', 'target_agent_id', 'objective', 'status']);
                }
            }
        }

        $provider = $this->providers->resolve((string) $route['provider']);
        $response = $provider->chat(new ChatRequest(
            provider: ProviderType::tryFrom((string) $route['provider']) ?? ProviderType::from((string) config('intelligence.default_provider')),
            model: $route['model'],
            messages: array_map(
                static fn (array $message): ChatMessage => new ChatMessage(
                    role: ChatRole::from($message['role']),
                    content: $message['content'],
                ),
                $this->promptAssembler->build($context),
            ),
            metadata: ['agent' => $agent?->slug, 'plan_id' => $plan->id],
        ));

        $verification = $this->verification->verify($trace, $stepPayloads, $toolResults);
        $summary = $this->buildSummary($planData->objective, $toolResults, $workflowExecutions, $verification);

        $trace->forceFill([
            'status' => $verification->passed ? 'completed' : 'needs_input',
            'completion_reason' => $verification->passed ? 'verified' : 'follow_up_required',
            'iterations' => count($stepPayloads),
            'duration_ms' => $response->usage->latencyMs,
            'input_tokens' => $response->usage->inputTokens,
            'output_tokens' => $response->usage->outputTokens,
            'step_payloads' => $stepPayloads,
            'verification_payload' => [
                'passed' => $verification->passed,
                'confidence_score' => $verification->confidenceScore,
                'missing_information' => $verification->missingInformation,
            ],
            'trace_payload' => array_merge($trace->trace_payload ?? [], [
                'completed_at' => now()->toIso8601String(),
                'tool_count' => count($toolResults),
                'workflow_count' => count($workflowExecutions),
                'delegation_count' => count($delegations),
                'summary' => $summary,
            ]),
            'delegation_payload' => $delegations,
        ])->save();

        $plan->forceFill([
            'status' => $trace->status,
            'completion_state' => $verification->passed ? 'fulfilled' : 'needs_input',
            'attempts' => count($stepPayloads),
        ])->save();

        $this->learningService->record([
            'workspace' => 'intelligence',
            'status' => $verification->passed ? 'completed' : 'needs_input',
            'outcome' => $verification->passed ? 'verified' : 'follow_up_required',
            'confidence' => $verification->confidenceScore,
            'latency_ms' => $response->usage->latencyMs,
            'tools_used' => array_map(fn ($toolResult): string => $toolResult->tool, $toolResults),
            'verification_results' => [
                'passed' => $verification->passed,
                'missing_information' => $verification->missingInformation,
            ],
            'planner_decisions' => [
                'objective' => $planData->objective,
                'required_tools' => $planData->requiredTools,
            ],
            'feedback_summary' => null,
            'metadata' => [
                'trace_id' => $trace->id,
                'conversation_id' => $conversation->id,
            ],
        ]);

        return [
            'agent' => $agent,
            'plan' => $plan,
            'trace' => $trace->refresh(),
            'response' => $response,
            'summary' => $summary,
            'tool_results' => $toolResults,
            'verification' => $verification,
        ];
    }

    private function executeStep(
        ExecutionPlanStep $step,
        string $objective,
        User $user,
        Conversation $conversation,
        ?Agent $agent,
        ExecutionTrace $trace,
        array &$toolResults,
        array &$workflowExecutions,
    ): array {
        $payload = [
            'objective' => $objective,
            'step' => $step->title,
            'step_kind' => $step->step_kind,
            'authorization_status' => 'authorized',
        ];

        $step->update(['status' => 'running', 'attempts' => $step->attempts + 1]);

        try {
            if ($step->step_kind === 'workflow') {
                $workflow = $this->workflowRuntime->start($step->title, $conversation, $agent, $step->required_tools ?? []);
                $workflow = $this->workflowRuntime->checkpoint($workflow, 'planned', ['objective' => $objective]);
                $workflow = $this->workflowRuntime->complete($workflow, ['objective' => $objective]);
                $workflowExecutions[] = $workflow->only(['id', 'name', 'status']);
                $payload['workflow_execution'] = $workflow->only(['id', 'name', 'status']);
                $payload['delegated'] = true;
            } elseif ($step->tool_slug !== null) {
                $toolResult = $this->toolExecutor->execute(
                    slug: $step->tool_slug,
                    payload: array_merge($payload, $step->input_payload ?? []),
                    context: new ToolContext(
                        user: $user,
                        conversation: $conversation,
                        agent: $agent,
                        memory: $trace->memory_payload ?? [],
                        metadata: ['trace_id' => $trace->id, 'step_id' => $step->id],
                    ),
                    trace: $trace,
                );

                $toolResults[] = $toolResult;
                $payload['tool_result'] = $toolResult->output;
                $payload['authorization_status'] = $toolResult->metadata['authorization']['status'] ?? 'authorized';
            }

            $payload['status'] = 'completed';
            $payload['verification_requirements'] = $step->verification_requirements ?? [];

            $step->update([
                'status' => 'completed',
                'output_payload' => $payload,
            ]);
        } catch (Throwable $exception) {
            $payload['status'] = 'failed';
            $payload['error'] = $exception->getMessage();
            $payload['authorization_status'] = str_contains(strtolower($exception->getMessage()), 'author')
                ? 'denied'
                : $payload['authorization_status'];

            $step->update([
                'status' => 'failed',
                'output_payload' => $payload,
                'verification_notes' => $exception->getMessage(),
            ]);
        }

        return $payload;
    }

    private function delegateIfPossible(ExecutionTrace $trace, ?Agent $agent, string $objective, array $stepPayload): ?\App\Domains\Intelligence\Models\AgentDelegation
    {
        if ($agent === null) {
            return null;
        }

        $targetAgent = Agent::query()
            ->where('id', '!=', $agent->id)
            ->where('status', 'active')
            ->orderBy('name')
            ->first();

        if ($targetAgent === null) {
            return null;
        }

        return $this->delegationService->delegate($trace, $agent, $targetAgent, $objective, $stepPayload);
    }

    private function buildSummary(string $objective, array $toolResults, array $workflowExecutions, VerificationResultData $verification): string
    {
        $parts = [
            "Objective: {$objective}.",
            sprintf('Executed %d tool step(s).', count($toolResults)),
        ];

        if ($workflowExecutions !== []) {
            $parts[] = sprintf('Orchestrated %d workflow execution(s).', count($workflowExecutions));
        }

        if (! $verification->passed) {
            $parts[] = 'Follow-up is required before the response can be treated as complete.';
        }

        return implode(' ', $parts);
    }

    /**
     * @return list<array{name: string, description: string, schema: array<string, mixed>}>
     */
    private function resolveAllowedTools(?Agent $agent): array
    {
        $tools = collect(app(ToolRegistry::class)->definitions());

        if ($agent !== null && $agent->allowed_tools !== null) {
            $tools = $tools->whereIn('slug', $agent->allowed_tools)->values();
        }

        return $tools->map(static fn ($tool): array => [
            'name' => $tool->slug,
            'description' => $tool->description ?? '',
            'schema' => $tool->input_schema ?? [],
        ])->all();
    }
}
