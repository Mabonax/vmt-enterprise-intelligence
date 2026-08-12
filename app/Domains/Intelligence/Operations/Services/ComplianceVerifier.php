<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Operations\Services;

use App\Domains\Intelligence\Operations\Models\EnterpriseMission;
use App\Domains\Intelligence\Operations\Models\MissionComplianceReview;

class ComplianceVerifier
{
    public function evaluateMission(EnterpriseMission $mission): MissionComplianceReview
    {
        $findings = [];

        if (($mission->mission_payload['sensitive_data'] ?? false) === true) {
            $findings[] = 'Sensitive data detected; retention and access rules must be enforced.';
        }

        $status = $findings === [] ? 'compliant' : 'conditional';
        $score = $findings === [] ? 95.0 : 76.0;

        return MissionComplianceReview::query()->create([
            'enterprise_mission_id' => $mission->id,
            'framework' => 'POPIA/GDPR/ISO',
            'status' => $status,
            'compliance_score' => $score,
            'findings' => $findings,
            'remediation_actions' => $findings === [] ? ['No remediation required.'] : ['Require approval, minimization, and audit trace before execution.'],
            'metadata' => [
                'retention_policy' => 'default',
                'security_classification' => $mission->mission_payload['security_classification'] ?? 'internal',
            ],
        ]);
    }
}
