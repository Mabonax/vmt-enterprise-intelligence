<?php

declare(strict_types=1);

namespace App\Http\Controllers\Intelligence;

use App\Domains\Intelligence\Models\BackgroundTask;
use App\Domains\Intelligence\Models\Conversation;
use App\Domains\Intelligence\Models\ExecutionTrace;
use App\Domains\Intelligence\Services\AgentExecutionService;
use App\Domains\Intelligence\Services\NullStreamingProvider;
use App\Http\Controllers\Controller;
use App\Jobs\ExecuteAgentRuntimeJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Str;

class RuntimeController extends Controller
{
    public function __construct(
        private readonly AgentExecutionService $executionService,
    ) {}

    public function execute(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'conversation_id' => ['required', 'string'],
            'prompt' => ['required', 'string'],
            'agent_id' => ['nullable', 'string'],
        ]);

        $conversation = Conversation::query()->findOrFail($validated['conversation_id']);
        $result = $this->executionService->execute($request->user(), $conversation, $validated['prompt'], $validated['agent_id'] ?? null);

        return response()->json([
            'trace_id' => $result['trace']->id,
            'plan_id' => $result['plan']->id,
            'status' => $result['trace']->status->value,
            'response' => $result['response']->message->content,
            'summary' => $result['summary'],
            'iterations' => $result['trace']->iterations,
            'steps' => $result['trace']->step_payloads,
            'verification' => [
                'passed' => $result['verification']->passed,
                'confidence_score' => $result['verification']->confidenceScore,
                'missing_information' => $result['verification']->missingInformation,
            ],
        ]);
    }

    public function replay(ExecutionTrace $executionTrace): JsonResponse
    {
        return response()->json([
            'trace' => $executionTrace->only(['id', 'status', 'provider', 'model', 'plan_payload', 'step_payloads', 'verification_payload', 'trace_payload', 'delegation_payload', 'metadata']),
        ]);
    }

    public function cancel(ExecutionTrace $executionTrace): JsonResponse
    {
        $executionTrace->update(['status' => 'cancelled', 'completion_reason' => 'cancelled_by_user']);

        return response()->json(['status' => 'cancelled']);
    }

    public function background(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'conversation_id' => ['required', 'string'],
            'prompt' => ['required', 'string'],
            'agent_id' => ['nullable', 'string'],
        ]);

        $task = BackgroundTask::query()->create([
            'id' => (string) Str::uuid(),
            'conversation_id' => $validated['conversation_id'],
            'type' => 'agent_execution',
            'status' => 'pending',
            'queue' => 'default',
            'payload' => $validated,
        ]);

        Bus::dispatch(new ExecuteAgentRuntimeJob($task->id, $request->user()->id, $validated['conversation_id'], $validated['prompt'], $validated['agent_id'] ?? null));

        return response()->json(['background_task_id' => $task->id, 'status' => 'queued']);
    }

    public function backgroundStatus(BackgroundTask $backgroundTask): JsonResponse
    {
        return response()->json(['task' => $backgroundTask]);
    }

    public function history(): JsonResponse
    {
        return response()->json([
            'history' => ExecutionTrace::query()
                ->latest()
                ->limit(25)
                ->get(['id', 'conversation_id', 'provider', 'model', 'status', 'completion_reason', 'iterations', 'created_at']),
        ]);
    }

    public function stream(ExecutionTrace $executionTrace): JsonResponse
    {
        $stream = new NullStreamingProvider([
            'trace_id' => $executionTrace->id,
            'status' => $executionTrace->status->value,
        ]);

        return response()->json([
            'chunks' => collect($stream->chunks())->map(fn ($chunk) => ['event' => $chunk->event, 'payload' => $chunk->payload])->all(),
        ]);
    }
}
