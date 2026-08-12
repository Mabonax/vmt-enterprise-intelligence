<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Services;

use App\Domains\Intelligence\Tools\Models\EnterpriseTool;
use App\Domains\Intelligence\Tools\Models\ToolHealth;

class ToolHealthMonitor
{
    public function record(EnterpriseTool $tool, bool $success, int $durationMs): ToolHealth
    {
        $current = ToolHealth::query()->firstOrNew(['enterprise_tool_id' => $tool->id]);
        $errors = (int) ($current->errors ?? 0) + ($success ? 0 : 1);
        $timeouts = (int) ($current->timeouts ?? 0);
        $totalRuns = max(1, (int) (($current->metadata['runs'] ?? 0) + 1));
        $successes = (int) (($current->metadata['successes'] ?? 0) + ($success ? 1 : 0));

        $current->fill([
            'availability' => $success,
            'latency_ms' => $durationMs,
            'timeouts' => $timeouts,
            'errors' => $errors,
            'average_duration_ms' => (int) round(((int) ($current->average_duration_ms ?? 0) + $durationMs) / $totalRuns),
            'success_rate' => $successes / $totalRuns,
            'last_execution_at' => now(),
            'last_failure_at' => $success ? $current->last_failure_at : now(),
            'health_score' => max(0, min(100, ($successes / $totalRuns) * 100)),
            'metadata' => ['runs' => $totalRuns, 'successes' => $successes],
        ])->save();

        return $current;
    }
}
