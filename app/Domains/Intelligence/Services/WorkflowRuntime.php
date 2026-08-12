<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Services;

use App\Domains\Intelligence\Models\Agent;
use App\Domains\Intelligence\Models\Conversation;
use App\Domains\Intelligence\Models\WorkflowExecution;

class WorkflowRuntime
{
    public function start(string $name, Conversation $conversation, ?Agent $agent, array $steps = []): WorkflowExecution
    {
        return WorkflowExecution::query()->create([
            'conversation_id' => $conversation->id,
            'agent_id' => $agent?->id,
            'name' => $name,
            'status' => 'running',
            'steps' => $steps,
            'approval_checkpoints' => [],
            'execution_history' => [[
                'event' => 'started',
                'at' => now()->toIso8601String(),
            ]],
            'metadata' => ['started_at' => now()->toIso8601String()],
        ]);
    }

    public function checkpoint(WorkflowExecution $execution, string $event, array $payload = []): WorkflowExecution
    {
        $history = $execution->execution_history ?? [];
        $history[] = [
            'event' => $event,
            'payload' => $payload,
            'at' => now()->toIso8601String(),
        ];

        $execution->forceFill([
            'execution_history' => $history,
            'metadata' => array_merge($execution->metadata ?? [], ['last_event' => $event]),
        ])->save();

        return $execution->refresh();
    }

    public function complete(WorkflowExecution $execution, array $metadata = []): WorkflowExecution
    {
        $history = $execution->execution_history ?? [];
        $history[] = [
            'event' => 'completed',
            'payload' => $metadata,
            'at' => now()->toIso8601String(),
        ];

        $execution->forceFill([
            'status' => 'completed',
            'execution_history' => $history,
            'metadata' => array_merge($execution->metadata ?? [], $metadata),
        ])->save();

        return $execution->refresh();
    }

    public function fail(WorkflowExecution $execution, string $reason, array $rollbackPayload = []): WorkflowExecution
    {
        $history = $execution->execution_history ?? [];
        $history[] = [
            'event' => 'failed',
            'payload' => ['reason' => $reason],
            'at' => now()->toIso8601String(),
        ];

        $execution->forceFill([
            'status' => 'failed',
            'rollback_payload' => $rollbackPayload,
            'execution_history' => $history,
            'metadata' => array_merge($execution->metadata ?? [], ['failure_reason' => $reason]),
        ])->save();

        return $execution->refresh();
    }
}
