<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Commercial\Services;

use App\Domains\Intelligence\Commercial\Models\DeploymentRunbook;
use App\Domains\Intelligence\Commercial\Models\DeploymentStep;
use App\Domains\Intelligence\Commercial\Models\IntelligenceTenant;

class DeploymentRunbookService
{
    public function create(IntelligenceTenant $tenant, ?string $proposalId = null): DeploymentRunbook
    {
        $runbook = DeploymentRunbook::query()->create([
            'intelligence_tenant_id' => $tenant->id,
            'intelligence_proposal_id' => $proposalId,
            'name' => $tenant->name.' deployment runbook',
            'status' => 'draft',
            'deployment_mode' => $tenant->deployment_mode ?? 'shared_saas',
            'runbook_payload' => ['source' => 'phase_nine'],
            'metadata' => [],
        ]);

        $sequence = 1;

        foreach ([
            'validate_package' => 'Validate package entitlements and quotas',
            'prepare_environment' => 'Prepare target deployment environment',
            'provision_workspace' => 'Provision workspace and branding',
            'handover_support' => 'Confirm support and SLA handover',
        ] as $stepKey => $title) {
            DeploymentStep::query()->create([
                'deployment_runbook_id' => $runbook->id,
                'step_key' => $stepKey,
                'title' => $title,
                'status' => 'pending',
                'sequence' => $sequence,
                'owner_name' => 'Deployment Operations',
                'metadata' => [],
            ]);

            $sequence++;
        }

        return $runbook->fresh('steps');
    }
}
