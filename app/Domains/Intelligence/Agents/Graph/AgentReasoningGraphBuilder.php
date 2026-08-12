<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Agents\Graph;

use App\Domains\Intelligence\Agents\Models\AgentTask;

class AgentReasoningGraphBuilder
{
    public function build(string $sessionId): array
    {
        $tasks = AgentTask::query()
            ->where('agent_session_id', $sessionId)
            ->orderBy('sequence')
            ->get(['id', 'parent_task_id', 'title', 'status', 'task_type']);

        $nodes = [];
        $edges = [];

        foreach ($tasks as $task) {
            $nodes[] = [
                'id' => $task->id,
                'label' => $task->title,
                'status' => $task->status,
                'type' => $task->task_type,
            ];

            if ($task->parent_task_id !== null) {
                $edges[] = [
                    'source' => $task->parent_task_id,
                    'target' => $task->id,
                    'relationship' => 'delegates_to',
                ];
            }
        }

        return ['nodes' => $nodes, 'edges' => $edges];
    }
}
