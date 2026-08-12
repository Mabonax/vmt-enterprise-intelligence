<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Agents\Services;

use App\Domains\Intelligence\Agents\Approvals\ApprovalGateService;
use App\Domains\Intelligence\Agents\Communication\AgentMessenger;
use App\Domains\Intelligence\Agents\DTOs\EnterpriseAgentExecutionData;
use App\Domains\Intelligence\Agents\Graph\AgentReasoningGraphBuilder;
use App\Domains\Intelligence\Agents\Models\AgentApproval;
use App\Domains\Intelligence\Agents\Models\AgentCapability;
use App\Domains\Intelligence\Agents\Models\AgentCheckpoint;
use App\Domains\Intelligence\Agents\Models\AgentCollaboration;
use App\Domains\Intelligence\Agents\Models\AgentContext;
use App\Domains\Intelligence\Agents\Models\AgentCostTracking;
use App\Domains\Intelligence\Agents\Models\AgentDecision;
use App\Domains\Intelligence\Agents\Models\AgentDelegation;
use App\Domains\Intelligence\Agents\Models\AgentExecutionLog;
use App\Domains\Intelligence\Agents\Models\AgentExecutionMetric;
use App\Domains\Intelligence\Agents\Models\AgentLearning;
use App\Domains\Intelligence\Agents\Models\AgentMemory;
use App\Domains\Intelligence\Agents\Models\AgentNotification;
use App\Domains\Intelligence\Agents\Models\AgentProfile;
use App\Domains\Intelligence\Agents\Models\AgentQualityScore;
use App\Domains\Intelligence\Agents\Models\AgentQueue;
use App\Domains\Intelligence\Agents\Models\AgentReplay;
use App\Domains\Intelligence\Agents\Models\AgentSession;
use App\Domains\Intelligence\Agents\Models\AgentState;
use App\Domains\Intelligence\Agents\Models\AgentSummary;
use App\Domains\Intelligence\Agents\Models\AgentTask;
use App\Domains\Intelligence\Agents\Models\AgentTaskAssignment;
use App\Domains\Intelligence\Agents\Models\AgentTeamMember;
use App\Domains\Intelligence\Agents\Models\AgentVerification;
use App\Domains\Intelligence\Agents\Models\AgentWorkflow;
use App\Domains\Intelligence\Agents\Models\AgentWorkflowStep;
use App\Domains\Intelligence\Agents\Planning\AgentRolePlanner;
use App\Domains\Intelligence\Agents\Reasoning\ReasoningTraceRecorder;
use App\Domains\Intelligence\Agents\Delegation\DelegationRouter;
use App\Domains\Intelligence\Knowledge\Services\KnowledgeRetrievalService;
use App\Domains\Intelligence\Knowledge\Services\LearningService;
use App\Domains\Intelligence\Knowledge\Services\MemoryService as KnowledgeMemoryService;
use App\Domains\Intelligence\Models\Agent;
use App\Domains\Intelligence\Services\PlanningEngine;
use App\Domains\Intelligence\Services\ToolExecutor;
use App\Domains\Intelligence\Services\ToolRegistry;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MultiAgentCoordinator
{
    public function __construct(
        private readonly AgentRoleCatalog $roleCatalog,
        private readonly AgentRolePlanner $rolePlanner,
        private readonly DelegationRouter $delegationRouter,
        private readonly AgentMessenger $messenger,
        private readonly ReasoningTraceRecorder $reasoning,
        private readonly ApprovalGateService $approvalGates,
        private readonly PlanningEngine $planning,
        private readonly ToolRegistry $toolRegistry,
        private readonly ToolExecutor $toolExecutor,
        private readonly KnowledgeRetrievalService $knowledgeRetrieval,
        private readonly KnowledgeMemoryService $knowledgeMemory,
        private readonly LearningService $learning,
        private readonly AgentReasoningGraphBuilder $graphBuilder,
    ) {}

    public function createAgent(User $user, array $attributes): Agent
    {
        return DB::transaction(function () use ($user, $attributes): Agent {
            $role = $this->roleCatalog->find((string) ($attributes['agent_role_key'] ?? 'planning_agent'));

            $agent = Agent::query()->create([
                'owner_user_id' => $user->id,
                'organization_id' => $user->organization_id,
                'name' => $attributes['name'],
                'slug' => $attributes['slug'],
                'description' => $attributes['description'] ?? null,
                'purpose' => $attributes['purpose'] ?? null,
                'system_instructions' => $attributes['system_instructions'] ?? $role?->default_prompt,
                'status' => $attributes['status'] ?? 'active',
                'visibility' => $attributes['visibility'] ?? 'organization',
                'default_provider' => $attributes['default_provider'] ?? config('intelligence.default_provider'),
                'default_model' => $attributes['default_model'] ?? config('intelligence.default_model'),
                'allowed_tools' => $attributes['allowed_tools'] ?? $role?->preferred_tools,
                'memory_enabled' => $attributes['memory_enabled'] ?? true,
                'metadata' => $attributes['metadata'] ?? [],
                'agent_role_key' => $role?->role_key,
                'reasoning_style' => $attributes['reasoning_style'] ?? $role?->reasoning_style ?? 'balanced',
                'risk_tolerance' => $attributes['risk_tolerance'] ?? $role?->risk_tolerance ?? 'moderate',
                'verification_strategy' => $attributes['verification_strategy'] ?? $role?->verification_strategy ?? 'reviewer',
                'memory_scope' => $attributes['memory_scope'] ?? $role?->memory_scope ?? 'shared',
                'delegation_enabled' => $attributes['delegation_enabled'] ?? true,
                'approval_required' => $attributes['approval_required'] ?? false,
            ]);

            AgentProfile::query()->updateOrCreate(
                ['agent_id' => $agent->id],
                [
                    'agent_role_id' => $role?->id,
                    'identity' => $attributes['identity'] ?? $agent->name,
                    'prompt' => $attributes['prompt'] ?? $agent->system_instructions,
                    'preferred_tools' => $attributes['allowed_tools'] ?? $role?->preferred_tools ?? [],
                    'capabilities' => $attributes['capabilities'] ?? $role?->capabilities ?? [],
                    'reasoning_style' => $agent->reasoning_style,
                    'risk_tolerance' => $agent->risk_tolerance,
                    'verification_strategy' => $agent->verification_strategy,
                    'memory_scope' => $agent->memory_scope,
                    'metadata' => $attributes['metadata'] ?? [],
                ],
            );

            AgentCapability::query()->where('agent_id', $agent->id)->delete();

            foreach (($attributes['capabilities'] ?? $role?->capabilities ?? []) as $capability) {
                AgentCapability::query()->create([
                    'agent_id' => $agent->id,
                    'agent_role_id' => $role?->id,
                    'capability_key' => (string) $capability,
                    'capability_type' => 'agent',
                    'confidence_weight' => 0.80,
                ]);
            }

            return $agent->refresh();
        });
    }

    public function startExecution(User $user, Agent $agent, EnterpriseAgentExecutionData $data): AgentSession
    {
        return DB::transaction(function () use ($user, $agent, $data): AgentSession {
            $session = AgentSession::query()->create([
                'agent_id' => $agent->id,
                'user_id' => $user->id,
                'session_key' => (string) Str::uuid(),
                'title' => $data->title,
                'objective' => $data->objective,
                'status' => $data->executionMode === 'immediate' ? 'running' : 'queued',
                'execution_mode' => $data->executionMode,
                'approval_role' => $data->approvalRole,
                'requires_human_approval' => $data->approvalRole !== null || $agent->approval_required,
                'started_at' => $data->executionMode === 'immediate' ? now() : null,
                'context_payload' => $data->context,
                'metadata' => $data->metadata,
            ]);

            AgentQueue::query()->create([
                'agent_session_id' => $session->id,
                'queue_name' => 'default',
                'status' => $data->executionMode === 'immediate' ? 'running' : 'queued',
                'available_at' => now(),
                'started_at' => $data->executionMode === 'immediate' ? now() : null,
                'metadata' => ['mode' => $data->executionMode],
            ]);

            AgentState::query()->create([
                'agent_id' => $agent->id,
                'agent_session_id' => $session->id,
                'current_status' => $session->status,
                'working_memory' => [],
                'metadata' => ['initiator_user_id' => $user->id],
            ]);

            AgentCheckpoint::query()->create([
                'agent_session_id' => $session->id,
                'checkpoint_type' => 'created',
                'status' => $session->status,
                'snapshot_payload' => ['objective' => $data->objective, 'title' => $data->title],
            ]);

            return $session;
        });
    }

    public function runSession(AgentSession $session): AgentSession
    {
        $agent = Agent::query()->findOrFail($session->agent_id);
        $user = User::query()->findOrFail($session->user_id);

        return DB::transaction(function () use ($session, $agent, $user): AgentSession {
            $session->forceFill(['status' => 'running', 'started_at' => $session->started_at ?? now()])->save();
            AgentState::query()->where('agent_session_id', $session->id)->update(['current_status' => 'running']);
            AgentQueue::query()->where('agent_session_id', $session->id)->update(['status' => 'running', 'started_at' => now(), 'attempts' => DB::raw('attempts + 1')]);

            $knowledge = $this->knowledgeRetrieval->retrieveForPrompt(
                $session->objective,
                [],
                (int) config('intelligence.knowledge.retrieval.default_limit', 5),
                'intelligence',
            );

            AgentContext::query()->create([
                'agent_id' => $agent->id,
                'agent_session_id' => $session->id,
                'context_key' => 'knowledge_retrieval',
                'context_payload' => $knowledge,
            ]);

            AgentMemory::query()->create([
                'agent_id' => $agent->id,
                'agent_session_id' => $session->id,
                'memory_key' => 'objective_brief',
                'memory_type' => 'shared',
                'scope' => $agent->memory_scope ?? 'shared',
                'title' => $session->title,
                'content' => $session->objective,
                'references' => $knowledge['results'] ?? [],
            ]);

            $teamRoleKeys = $this->rolePlanner->buildTeam($session->objective);
            $conversation = $this->messenger->open($session->id, $session->title, $teamRoleKeys);
            $workflow = AgentWorkflow::query()->create([
                'agent_session_id' => $session->id,
                'lead_agent_id' => $agent->id,
                'name' => $session->title,
                'status' => 'running',
                'execution_mode' => $session->execution_mode,
                'approval_gates' => ['requested_role' => $session->approval_role],
                'branching_rules' => ['loop_protection' => true, 'retry' => true],
                'metadata' => ['team_roles' => $teamRoleKeys],
            ]);

            foreach ($teamRoleKeys as $index => $roleKey) {
                $role = $this->roleCatalog->find($roleKey);

                AgentTeamMember::query()->create([
                    'agent_session_id' => $session->id,
                    'agent_id' => $roleKey === $agent->agent_role_key ? $agent->id : null,
                    'agent_role_id' => $role?->id,
                    'member_name' => $role?->name ?? str($roleKey)->headline()->toString(),
                    'member_type' => $index === 0 ? 'lead' : 'specialist',
                    'status' => 'active',
                    'order_column' => $index + 1,
                ]);
            }

            $plan = $this->planning->plan($session->objective, $agent, [
                'session_id' => $session->id,
                'knowledge_summary' => $knowledge['summary'] ?? '',
            ]);

            $chain = $this->reasoning->start(
                $session->id,
                $agent->id,
                $session->objective,
                [
                    'objective' => $plan->objective,
                    'required_tools' => $plan->requiredTools,
                    'steps' => array_map(static fn ($step) => $step->toArray(), $plan->steps),
                ],
                $knowledge['results'] ?? [],
            );

            $this->reasoning->snapshot($chain, 'team', 'Team assembled for execution.', ['roles' => $teamRoleKeys]);

            if ($session->requires_human_approval) {
                $approval = $this->approvalGates->request(
                    $session,
                    $workflow->id,
                    $user,
                    'human',
                    $session->approval_role ?? 'manager',
                    'Execution requires approval before active orchestration can continue.',
                );

                AgentNotification::query()->create([
                    'agent_session_id' => $session->id,
                    'user_id' => $user->id,
                    'type' => 'approval_requested',
                    'status' => 'pending',
                    'message' => 'Execution paused pending human approval.',
                    'payload' => ['approval_id' => $approval->id],
                ]);

                $session->forceFill(['status' => 'paused'])->save();
                $workflow->forceFill(['status' => 'paused'])->save();
                AgentState::query()->where('agent_session_id', $session->id)->update(['current_status' => 'paused']);
                AgentCheckpoint::query()->create([
                    'agent_session_id' => $session->id,
                    'checkpoint_type' => 'approval_wait',
                    'status' => 'paused',
                    'snapshot_payload' => ['approval_id' => $approval->id],
                ]);

                return $session->refresh();
            }

            $executedTools = [];
            $stepOutcomes = [];
            $delegations = [];

            foreach ($plan->steps as $stepData) {
                $task = AgentTask::query()->create([
                    'agent_session_id' => $session->id,
                    'owner_agent_id' => $agent->id,
                    'title' => $stepData->title,
                    'objective' => $session->objective,
                    'status' => 'running',
                    'priority' => 'normal',
                    'task_type' => $stepData->stepKind,
                    'sequence' => $stepData->sequence,
                    'dependencies' => $stepData->dependencies,
                    'input_payload' => $stepData->inputPayload,
                    'metadata' => $stepData->metadata,
                ]);

                $roleKey = $this->delegationRouter->roleForStep($stepData);
                $role = $this->roleCatalog->find($roleKey);

                AgentTaskAssignment::query()->create([
                    'agent_task_id' => $task->id,
                    'agent_id' => $roleKey === $agent->agent_role_key ? $agent->id : null,
                    'agent_role_id' => $role?->id,
                    'assignment_type' => 'primary',
                    'status' => 'assigned',
                ]);

                $step = AgentWorkflowStep::query()->create([
                    'agent_workflow_id' => $workflow->id,
                    'agent_task_id' => $task->id,
                    'name' => $stepData->title,
                    'sequence' => $stepData->sequence,
                    'status' => 'running',
                    'step_kind' => $stepData->stepKind,
                    'is_parallel' => $stepData->stepKind === 'tool_call' && count($plan->requiredTools) > 1,
                    'requires_approval' => false,
                    'condition_payload' => ['dependencies' => $stepData->dependencies],
                ]);

                $this->messenger->send(
                    $conversation,
                    $agent->id,
                    null,
                    'request',
                    $stepData->title,
                    'Delegated execution step',
                    $knowledge['results'] ?? [],
                    [],
                    ['role_key' => $roleKey],
                );

                $toolOutput = [];
                if ($stepData->toolSlug !== null) {
                    try {
                        $toolResult = $this->toolExecutor->execute(
                            (string) $stepData->toolSlug,
                            array_merge($stepData->inputPayload, ['objective' => $session->objective]),
                            new \App\Domains\Intelligence\DTOs\ToolContext($user, null, $agent, $knowledge['results'] ?? [], ['agent_session_id' => $session->id]),
                        );

                        $toolOutput = $toolResult->output;
                        $executedTools[] = $toolResult->tool;

                        AgentExecutionMetric::query()->create([
                            'agent_session_id' => $session->id,
                            'agent_id' => $agent->id,
                            'metric_key' => 'latency_ms',
                            'metric_value' => $toolResult->durationMs,
                            'unit' => 'ms',
                            'metadata' => ['tool' => $toolResult->tool],
                        ]);
                    } catch (\Throwable $exception) {
                        $toolOutput = ['error' => $exception->getMessage()];
                    }
                }

                $delegation = AgentDelegation::query()->create([
                    'agent_session_id' => $session->id,
                    'source_agent_id' => $agent->id,
                    'target_agent_id' => null,
                    'agent_task_id' => $task->id,
                    'status' => 'completed',
                    'objective' => $stepData->title,
                    'handover_payload' => ['role_key' => $roleKey],
                    'result_payload' => $toolOutput,
                ]);
                $delegations[] = $delegation->id;

                AgentCollaboration::query()->create([
                    'agent_session_id' => $session->id,
                    'source_agent_id' => $agent->id,
                    'target_agent_id' => null,
                    'collaboration_type' => 'delegation',
                    'payload' => ['task_id' => $task->id, 'role_key' => $roleKey],
                ]);

                AgentExecutionLog::query()->create([
                    'agent_session_id' => $session->id,
                    'agent_workflow_step_id' => $step->id,
                    'agent_id' => $agent->id,
                    'event' => 'step_completed',
                    'status' => empty($toolOutput['error']) ? 'completed' : 'failed',
                    'message' => $stepData->title,
                    'payload' => ['tool_output' => $toolOutput, 'role_key' => $roleKey],
                ]);

                $taskStatus = empty($toolOutput['error']) ? 'completed' : 'failed';

                $task->forceFill(['status' => $taskStatus, 'output_payload' => $toolOutput])->save();
                $step->forceFill(['status' => $taskStatus, 'result_payload' => $toolOutput])->save();

                $this->reasoning->snapshot(
                    $chain,
                    $stepData->stepKind,
                    $stepData->title,
                    ['tool' => $stepData->toolSlug, 'output' => $toolOutput],
                    ['role_key' => $roleKey],
                );

                $this->messenger->send(
                    $conversation,
                    null,
                    $agent->id,
                    'response',
                    $stepData->title.' completed.',
                    'Execution update',
                    [],
                    $toolOutput,
                    ['task_id' => $task->id],
                );

                $stepOutcomes[] = ['title' => $stepData->title, 'status' => $taskStatus, 'tool' => $stepData->toolSlug];
            }

            $toolCoverage = array_values(array_unique($executedTools));
            $missingTools = array_values(array_diff($plan->requiredTools, $toolCoverage));
            $confidence = max(0.45, min(0.98, 0.92 - (count($missingTools) * 0.12)));

            AgentVerification::query()->create([
                'agent_session_id' => $session->id,
                'status' => $missingTools === [] ? 'verified' : 'needs_input',
                'confidence' => $confidence,
                'missing_evidence' => $missingTools,
                'tool_coverage' => $toolCoverage,
                'knowledge_coverage' => collect($knowledge['results'] ?? [])->pluck('title')->values()->all(),
            ]);

            AgentQualityScore::query()->create([
                'agent_session_id' => $session->id,
                'completeness_score' => $missingTools === [] ? 0.92 : 0.68,
                'evidence_score' => ($knowledge['results'] ?? []) === [] ? 0.55 : 0.86,
                'policy_score' => 0.90,
                'verification_score' => $confidence,
                'overall_score' => round((($missingTools === [] ? 0.92 : 0.68) + (($knowledge['results'] ?? []) === [] ? 0.55 : 0.86) + 0.90 + $confidence) / 4, 2),
            ]);

            AgentDecision::query()->create([
                'agent_session_id' => $session->id,
                'agent_id' => $agent->id,
                'decision_key' => 'execution_strategy',
                'why' => 'Selected a planner-led team and delegated by step kind to preserve additive runtime integration.',
                'alternatives' => ['single_agent', 'manual_workflow', 'tool_only'],
                'chosen_path' => 'multi_agent_orchestration',
                'confidence' => $confidence,
                'risks' => $missingTools,
                'references' => collect($knowledge['results'] ?? [])->pluck('id')->values()->all(),
                'tool_evidence' => $toolCoverage,
                'knowledge_evidence' => $knowledge['results'] ?? [],
            ]);

            $summaryText = 'Planned '.count($plan->steps).' step(s), executed '.count($toolCoverage).' tool-backed step(s), and coordinated '.count($teamRoleKeys).' team role(s).';

            AgentSummary::query()->create([
                'agent_session_id' => $session->id,
                'summary_type' => 'final',
                'summary' => $summaryText,
                'highlights' => $stepOutcomes,
            ]);

            AgentReplay::query()->create([
                'agent_session_id' => $session->id,
                'timeline' => AgentExecutionLog::query()->where('agent_session_id', $session->id)->orderBy('created_at')->get(['event', 'status', 'message', 'created_at'])->toArray(),
                'reasoning_graph' => $this->graphBuilder->build($session->id),
            ]);

            AgentCostTracking::query()->create([
                'agent_session_id' => $session->id,
                'agent_id' => $agent->id,
                'input_tokens' => max(1, str($session->objective)->length()),
                'output_tokens' => max(1, str($summaryText)->length()),
                'estimated_cost' => round(count($toolCoverage) * 0.01, 4),
                'tool_costs' => array_map(static fn (string $tool): array => ['tool' => $tool, 'cost' => 0.01], $toolCoverage),
            ]);

            AgentLearning::query()->create([
                'agent_session_id' => $session->id,
                'learning_type' => 'execution',
                'summary' => $summaryText,
                'best_practices' => ['Use planning before delegation', 'Record knowledge context before execution'],
                'workflow_templates' => ['planner -> specialist -> reviewer'],
                'decision_templates' => ['execution_strategy'],
            ]);

            $this->knowledgeMemory->remember([
                'title' => $session->title,
                'summary' => $summaryText,
                'content' => $session->objective,
                'owner_type' => AgentSession::class,
                'owner_id' => $session->id,
                'citations' => $knowledge['results'] ?? [],
                'created_from' => ['phase' => 'phase_7_multi_agent'],
                'metadata' => ['tool_coverage' => $toolCoverage],
            ]);

            $this->learning->record([
                'workspace' => 'intelligence',
                'status' => 'completed',
                'outcome' => 'multi_agent_execution',
                'confidence' => $confidence,
                'latency_ms' => (int) AgentExecutionMetric::query()->where('agent_session_id', $session->id)->where('metric_key', 'latency_ms')->sum('metric_value'),
                'tools_used' => $toolCoverage,
                'verification_results' => ['missing_tools' => $missingTools],
                'planner_decisions' => ['team_roles' => $teamRoleKeys],
                'metadata' => ['agent_session_id' => $session->id],
            ]);

            AgentNotification::query()->create([
                'agent_session_id' => $session->id,
                'user_id' => $user->id,
                'type' => 'execution_completed',
                'status' => 'sent',
                'message' => 'Multi-agent execution completed.',
                'payload' => ['session_id' => $session->id],
            ]);

            $session->forceFill([
                'status' => $missingTools === [] ? 'completed' : 'needs_input',
                'completed_at' => now(),
                'result_payload' => [
                    'summary' => $summaryText,
                    'tool_coverage' => $toolCoverage,
                    'missing_tools' => $missingTools,
                    'delegations' => $delegations,
                ],
            ])->save();

            $workflow->forceFill(['status' => $session->status])->save();
            AgentQueue::query()->where('agent_session_id', $session->id)->update(['status' => $session->status, 'completed_at' => now()]);
            AgentState::query()->where('agent_session_id', $session->id)->update(['current_status' => $session->status]);
            AgentCheckpoint::query()->create([
                'agent_session_id' => $session->id,
                'checkpoint_type' => 'completed',
                'status' => $session->status,
                'snapshot_payload' => $session->result_payload,
            ]);

            return $session->refresh();
        });
    }

    public function pause(AgentSession $session): AgentSession
    {
        $session->forceFill(['status' => 'paused'])->save();
        AgentState::query()->where('agent_session_id', $session->id)->update(['current_status' => 'paused']);
        AgentQueue::query()->where('agent_session_id', $session->id)->update(['status' => 'paused']);

        return $session->refresh();
    }

    public function resume(AgentSession $session): AgentSession
    {
        $session->forceFill(['status' => 'queued'])->save();
        AgentState::query()->where('agent_session_id', $session->id)->update(['current_status' => 'queued']);
        AgentQueue::query()->where('agent_session_id', $session->id)->update(['status' => 'queued']);

        return $session->refresh();
    }

    public function cancel(AgentSession $session): AgentSession
    {
        $session->forceFill(['status' => 'cancelled', 'completed_at' => now()])->save();
        AgentState::query()->where('agent_session_id', $session->id)->update(['current_status' => 'cancelled']);
        AgentQueue::query()->where('agent_session_id', $session->id)->update(['status' => 'cancelled', 'completed_at' => now()]);

        return $session->refresh();
    }

    public function approve(AgentApproval $approval, User $user, ?string $notes = null): AgentApproval
    {
        $approval = $this->approvalGates->approve($approval, $user, $notes);
        $session = AgentSession::query()->find($approval->agent_session_id);

        if ($session !== null) {
            DB::table('agent_sessions')->where('id', $session->id)->update(['status' => 'queued', 'updated_at' => now()]);
            DB::table('agent_queue')->where('agent_session_id', $session->id)->update(['status' => 'queued', 'updated_at' => now()]);
            DB::table('agent_state')->where('agent_session_id', $session->id)->update(['current_status' => 'queued', 'updated_at' => now()]);
            AgentNotification::query()->create([
                'agent_session_id' => $session->id,
                'user_id' => $user->id,
                'type' => 'approval_approved',
                'status' => 'sent',
                'message' => 'Execution approval granted. Session returned to queue.',
                'payload' => ['approval_id' => $approval->id],
            ]);
        }

        return $approval;
    }

    public function reject(AgentApproval $approval, User $user, ?string $notes = null): AgentApproval
    {
        $approval = $this->approvalGates->reject($approval, $user, $notes);
        $session = AgentSession::query()->find($approval->agent_session_id);

        if ($session !== null) {
            DB::table('agent_sessions')->where('id', $session->id)->update(['status' => 'cancelled', 'completed_at' => now(), 'updated_at' => now()]);
            DB::table('agent_queue')->where('agent_session_id', $session->id)->update(['status' => 'cancelled', 'completed_at' => now(), 'updated_at' => now()]);
            DB::table('agent_state')->where('agent_session_id', $session->id)->update(['current_status' => 'cancelled', 'updated_at' => now()]);
            AgentNotification::query()->create([
                'agent_session_id' => $session->id,
                'user_id' => $user->id,
                'type' => 'approval_rejected',
                'status' => 'sent',
                'message' => 'Execution approval rejected. Session cancelled.',
                'payload' => ['approval_id' => $approval->id],
            ]);
        }

        return $approval;
    }
}
