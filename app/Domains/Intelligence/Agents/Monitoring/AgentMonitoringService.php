<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Agents\Monitoring;

use App\Domains\Intelligence\Agents\Models\AgentApproval;
use App\Domains\Intelligence\Agents\Models\AgentExecutionMetric;
use App\Domains\Intelligence\Agents\Models\AgentQualityScore;
use App\Domains\Intelligence\Agents\Models\AgentQueue;
use App\Domains\Intelligence\Agents\Models\AgentSession;

class AgentMonitoringService
{
    public function summary(): array
    {
        return [
            'running_sessions' => AgentSession::query()->where('status', 'running')->count(),
            'queued_sessions' => AgentQueue::query()->where('status', 'queued')->count(),
            'paused_sessions' => AgentSession::query()->where('status', 'paused')->count(),
            'pending_approvals' => AgentApproval::query()->where('status', 'pending')->count(),
            'average_quality' => round((float) AgentQualityScore::query()->avg('overall_score'), 2),
            'average_latency_ms' => round((float) AgentExecutionMetric::query()->where('metric_key', 'latency_ms')->avg('metric_value'), 2),
        ];
    }
}
