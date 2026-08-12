<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Commercial\Services;

use App\Domains\Intelligence\Commercial\Models\DeploymentRunbook;
use App\Domains\Intelligence\Commercial\Models\IntelligenceTenant;
use App\Domains\Intelligence\Commercial\Models\ReleaseReadinessCheck;

class ReleaseReadinessService
{
    public function check(IntelligenceTenant $tenant): array
    {
        $runbook = DeploymentRunbook::query()->where('intelligence_tenant_id', $tenant->id)->latest()->first();
        $score = 0;
        $checks = [
            'workspace' => $tenant->workspaces()->exists(),
            'security' => $tenant->securityProfile()->exists(),
            'branding' => $tenant->brandingProfile()->exists(),
            'deployment' => $tenant->deploymentProfile()->exists(),
            'runbook' => $runbook !== null,
        ];

        foreach ($checks as $passed) {
            $score += $passed ? 20 : 0;
        }

        $record = ReleaseReadinessCheck::query()->create([
            'intelligence_tenant_id' => $tenant->id,
            'deployment_runbook_id' => $runbook?->id,
            'status' => $score === 100 ? 'ready' : 'review_required',
            'score' => $score,
            'check_payload' => $checks,
            'checked_at' => now(),
            'metadata' => [],
        ]);

        return $record->toArray();
    }
}