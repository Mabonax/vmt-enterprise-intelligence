<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\AdminConsole\Services;

use App\Domains\Intelligence\AdminConsole\Enums\AdminConsoleHealthStatus;
use App\Domains\Intelligence\Commercial\Models\DeploymentRunbook;
use App\Domains\Intelligence\Commercial\Models\IntelligenceSubscription;
use App\Domains\Intelligence\Commercial\Models\IntelligenceTenant;
use App\Domains\Intelligence\Commercial\Models\ReleaseReadinessCheck;
use App\Domains\Intelligence\Commercial\Models\SupportTicket;

class AdminConsoleReadinessService
{
    public function summary(): array
    {
        $checks = [
            'commercial_readiness' => IntelligenceSubscription::query()->where('status', 'active')->count() > 0,
            'tenant_onboarding_readiness' => IntelligenceTenant::query()->where('status', 'active')->count() > 0,
            'package_entitlement_readiness' => IntelligenceSubscription::query()->count() >= IntelligenceTenant::query()->where('status', 'active')->count(),
            'deployment_readiness' => DeploymentRunbook::query()->where('status', 'ready')->exists(),
            'support_readiness' => SupportTicket::query()->whereIn('status', ['new', 'open', 'escalated'])->count() < 5,
            'compliance_readiness' => ReleaseReadinessCheck::query()->where('status', 'blocked')->count() === 0,
            'monitoring_readiness' => true,
        ];

        $blockers = [];

        foreach ($checks as $key => $ready) {
            if (! $ready) {
                $blockers[] = str($key)->replace('_', ' ')->title()->toString().' needs attention.';
            }
        }

        $recommendations = $blockers === []
            ? ['Maintain current release posture and continue health snapshot refresh cycles.']
            : array_map(static fn (string $blocker): string => 'Address blocker: '.$blocker, $blockers);

        return [
            'overall_status' => $blockers === [] ? AdminConsoleHealthStatus::Healthy->value : AdminConsoleHealthStatus::AtRisk->value,
            'checks' => $checks,
            'blockers' => $blockers,
            'recommendations' => $recommendations,
        ];
    }
}
