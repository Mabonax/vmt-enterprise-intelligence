<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Domains\Intelligence\Models\BackgroundTask;
use App\Domains\Intelligence\Models\Conversation;
use App\Domains\Intelligence\Services\AgentExecutionService;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ExecuteAgentRuntimeJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $backgroundTaskId,
        private readonly int $userId,
        private readonly string $conversationId,
        private readonly string $prompt,
        private readonly ?string $agentId = null,
    ) {}

    public function handle(AgentExecutionService $executionService): void
    {
        $task = BackgroundTask::query()->findOrFail($this->backgroundTaskId);
        $task->update(['status' => 'running', 'progress' => 25, 'started_at' => now()]);

        $result = $executionService->execute(
            user: User::query()->findOrFail($this->userId),
            conversation: Conversation::query()->findOrFail($this->conversationId),
            prompt: $this->prompt,
            agentId: $this->agentId,
        );

        $task->update([
            'status' => 'completed',
            'progress' => 100,
            'completed_at' => now(),
            'execution_trace_id' => $result['trace']->id,
            'result_payload' => [
                'trace_id' => $result['trace']->id,
                'plan_id' => $result['plan']->id,
            ],
        ]);
    }
}
