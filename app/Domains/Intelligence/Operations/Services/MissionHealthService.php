<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Operations\Services;

use App\Domains\Intelligence\Operations\Models\EnterpriseMission;

class MissionHealthService
{
    public function score(EnterpriseMission $mission): float
    {
        $riskPenalty = (float) $mission->risks()->avg('impact_score');
        $progress = (float) $mission->completion_percentage;
        $deadlinePenalty = $mission->deadline_at !== null && now()->gt($mission->deadline_at) ? 20.0 : 0.0;

        return max(0.0, min(100.0, 70.0 + ($progress * 0.3) - ($riskPenalty * 0.6) - $deadlinePenalty));
    }
}
