<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Domains\Intelligence\Knowledge\Services\LearningService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class LearningCycleJob implements ShouldQueue
{
    use Queueable;

    public function handle(LearningService $learning): void
    {
        $learning->record([
            'status' => 'completed',
            'outcome' => 'maintenance_cycle',
            'latency_ms' => 0,
            'tools_used' => [],
            'verification_results' => [],
            'planner_decisions' => [],
            'feedback_summary' => 'Scheduled learning cycle completed.',
            'metadata' => ['source' => 'scheduler'],
        ]);
    }
}
