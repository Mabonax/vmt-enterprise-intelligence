<?php

declare(strict_types=1);

namespace App\Http\Controllers\Intelligence;

use App\Domains\Intelligence\Agents\DTOs\EnterpriseAgentExecutionData;
use App\Domains\Intelligence\Agents\Models\AgentApproval;
use App\Domains\Intelligence\Agents\Models\AgentMemory;
use App\Domains\Intelligence\Agents\Models\AgentMessage;
use App\Domains\Intelligence\Agents\Models\AgentReasoningChain;
use App\Domains\Intelligence\Agents\Models\AgentSession;
use App\Domains\Intelligence\Agents\Models\AgentTask;
use App\Domains\Intelligence\Agents\Models\AgentTeamMember;
use App\Domains\Intelligence\Agents\Models\AgentWorkflow;
use App\Domains\Intelligence\Agents\Resources\AgentSessionResource;
use App\Domains\Intelligence\Agents\Resources\EnterpriseAgentResource;
use App\Domains\Intelligence\Agents\Services\AgentRoleCatalog;
use App\Domains\Intelligence\Agents\Services\MultiAgentCoordinator;
use App\Domains\Intelligence\Models\Agent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Intelligence\EnterpriseAgentStoreRequest;
use App\Http\Requests\Intelligence\MultiAgentExecutionRequest;
use App\Jobs\ExecuteMultiAgentSessionJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;

class MultiAgentController extends Controller
{
    public function __construct(
        private readonly MultiAgentCoordinator $coordinator,
        private readonly AgentRoleCatalog $roles,
    ) {}

    public function store(EnterpriseAgentStoreRequest $request): JsonResponse
    {
        $agent = $this->coordinator->createAgent($request->user(), $request->validated());

        return response()->json(['agent' => EnterpriseAgentResource::make($agent)], 201);
    }

    public function start(MultiAgentExecutionRequest $request): JsonResponse
    {
        $agent = Agent::query()->findOrFail((string) $request->string('agent_id'));
        $session = $this->coordinator->startExecution(
            $request->user(),
            $agent,
            new EnterpriseAgentExecutionData(
                objective: (string) $request->string('objective'),
                title: (string) $request->string('title'),
                executionMode: (string) $request->string('execution_mode', 'queued'),
                approvalRole: $request->filled('approval_role') ? (string) $request->string('approval_role') : null,
                context: $request->input('context', []),
                metadata: $request->input('metadata', []),
            ),
        );

        if ($session->execution_mode === 'immediate') {
            $session = $this->coordinator->runSession($session);
        } else {
            Bus::dispatch(new ExecuteMultiAgentSessionJob($session->id));
        }

        return response()->json(['session' => AgentSessionResource::make($session)], 201);
    }

    public function delegate(Request $request, AgentSession $session): JsonResponse
    {
        $validated = $request->validate([
            'task_id' => ['required', 'string', 'exists:agent_tasks,id'],
        ]);

        $task = AgentTask::query()->findOrFail($validated['task_id']);

        return response()->json([
            'delegation' => [
                'task_id' => $task->id,
                'status' => $task->status,
                'session_id' => $session->id,
            ],
        ]);
    }

    public function pause(AgentSession $session): JsonResponse
    {
        return response()->json(['session' => AgentSessionResource::make($this->coordinator->pause($session))]);
    }

    public function resume(AgentSession $session): JsonResponse
    {
        return response()->json(['session' => AgentSessionResource::make($this->coordinator->resume($session))]);
    }

    public function cancel(AgentSession $session): JsonResponse
    {
        return response()->json(['session' => AgentSessionResource::make($this->coordinator->cancel($session))]);
    }

    public function approve(Request $request, AgentApproval $approval): JsonResponse
    {
        $validated = $request->validate(['notes' => ['nullable', 'string']]);

        return response()->json([
            'approval' => $this->coordinator->approve($approval, $request->user(), $validated['notes'] ?? null),
        ]);
    }

    public function reject(Request $request, AgentApproval $approval): JsonResponse
    {
        $validated = $request->validate(['notes' => ['nullable', 'string']]);

        return response()->json([
            'approval' => $this->coordinator->reject($approval, $request->user(), $validated['notes'] ?? null),
        ]);
    }

    public function workflows(): JsonResponse
    {
        return response()->json(['workflows' => AgentWorkflow::query()->latest()->limit(25)->get()]);
    }

    public function messages(): JsonResponse
    {
        return response()->json(['messages' => AgentMessage::query()->latest()->limit(50)->get()]);
    }

    public function teams(): JsonResponse
    {
        return response()->json(['teams' => AgentTeamMember::query()->latest()->limit(50)->get()]);
    }

    public function memory(): JsonResponse
    {
        return response()->json(['memory' => AgentMemory::query()->latest()->limit(50)->get()]);
    }

    public function reasoning(): JsonResponse
    {
        return response()->json(['reasoning' => AgentReasoningChain::query()->latest()->limit(25)->get()]);
    }

    public function executions(): JsonResponse
    {
        return response()->json(['executions' => AgentSession::query()->latest()->limit(25)->get()]);
    }

    public function roles(): JsonResponse
    {
        return response()->json(['roles' => $this->roles->definitions()]);
    }
}
