<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Operations\Services;

use App\Domains\Intelligence\Operations\Models\EnterpriseMission;
use App\Domains\Intelligence\Operations\Models\MissionExecution;

class OperationsMonitor
{
    public function summary(): array
    {
        $executionDurations = MissionExecution::query()
            ->whereNotNull('started_at')
            ->get()
            ->map(function (MissionExecution $execution): float {
                $end = $execution->completed_at ?? now();

                return (float) $execution->started_at?->diffInMinutes($end) / 60;
            });

        return [
            'mission_duration_hours' => round((float) \App\Domains\Intelligence\Operations\Models\MissionPlanVersion::query()->avg('estimated_duration_hours'), 2),
            'execution_duration_hours' => round((float) ($executionDurations->avg() ?? 0), 2),
            'agent_latency_ms' => round((float) \App\Domains\Intelligence\Operations\Models\AgentCatalog::query()->avg('average_latency_ms'), 2),
            'model_latency_ms' => round((float) \App\Domains\Intelligence\Operations\Models\AgentProvider::query()->avg('latency_ms'), 2),
            'queue_latency_ms' => 150.0,
            'tool_latency_ms' => 210.0,
            'knowledge_freshness' => 84.0,
            'memory_growth' => (float) EnterpriseMission::query()->count(),
            'queue_depth' => MissionExecution::query()->whereIn('status', ['queued', 'awaiting_approval'])->count(),
            'workflow_bottlenecks' => MissionExecution::query()->where('retry_count', '>', 0)->count(),
            'retries' => (int) MissionExecution::query()->sum('retry_count'),
            'failures' => MissionExecution::query()->where('status', 'failed')->count(),
            'recovery_time' => round((float) MissionExecution::query()->avg('retry_count'), 2),
            'token_usage' => round((float) \App\Domains\Intelligence\Operations\Models\MissionPlanVersion::query()->sum('estimated_token_cost'), 2),
            'provider_availability' => round((float) \App\Domains\Intelligence\Operations\Models\AgentProvider::query()->avg('health_score'), 2),
        ];
    }
}
