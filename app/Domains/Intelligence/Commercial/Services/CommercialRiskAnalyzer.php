<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Commercial\Services;

class CommercialRiskAnalyzer
{
    public function analyze(string $deploymentMode, bool $requiresDataResidency): array
    {
        $risks = [];

        if ($deploymentMode === 'on_premise') {
            $risks[] = ['risk' => 'Client-managed infrastructure maturity', 'severity' => 'high'];
        }

        if ($requiresDataResidency) {
            $risks[] = ['risk' => 'Regional hosting and compliance review required', 'severity' => 'medium'];
        }

        if ($risks === []) {
            $risks[] = ['risk' => 'Standard commercialization posture', 'severity' => 'low'];
        }

        return $risks;
    }
}