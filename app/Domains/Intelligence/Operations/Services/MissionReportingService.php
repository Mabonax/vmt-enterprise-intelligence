<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Operations\Services;

use App\Domains\Intelligence\Operations\Models\EnterpriseMission;

class MissionReportingService
{
    public function report(EnterpriseMission $mission): array
    {
        return [
            'mission' => $mission->only(['id', 'mission_key', 'title', 'status', 'priority', 'completion_percentage']),
            'plan_versions' => $mission->planVersions()->count(),
            'executions' => $mission->executions()->count(),
            'approvals_pending' => $mission->approvals()->where('status', 'pending')->count(),
            'latest_health_score' => $mission->health_score,
            'latest_risk_score' => $mission->risk_score,
            'decision_records' => $mission->decisions()->count(),
            'predictions' => $mission->predictions()->count(),
            'simulations' => $mission->simulations()->count(),
            'timeline' => $mission->checkpoints()->latest('recorded_at')->limit(10)->get(['checkpoint_type', 'status', 'recorded_at'])->toArray(),
        ];
    }
}
