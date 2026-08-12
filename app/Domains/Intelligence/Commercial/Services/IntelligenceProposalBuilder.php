<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Commercial\Services;

use App\Domains\Intelligence\Commercial\DTOs\ProposalPayloadData;
use App\Domains\Intelligence\Commercial\Models\IntelligencePackage;
use App\Domains\Intelligence\Commercial\Models\IntelligenceProposal;
use App\Domains\Intelligence\Commercial\Models\IntelligenceTenant;
use App\Domains\Intelligence\Commercial\Models\ProposalAssumption;
use App\Domains\Intelligence\Commercial\Models\ProposalDeploymentLine;
use App\Domains\Intelligence\Commercial\Models\ProposalPackageLine;
use App\Domains\Intelligence\Commercial\Models\ProposalRisk;
use App\Domains\Intelligence\Commercial\Models\ProposalServiceLine;

class IntelligenceProposalBuilder
{
    public function __construct(
        private readonly PricingEstimator $pricing,
        private readonly CommercialRiskAnalyzer $riskAnalyzer,
    ) {}

    public function build(IntelligenceTenant $tenant, IntelligencePackage $package, string $deploymentMode): IntelligenceProposal
    {
        $pricing = $this->pricing->estimate($package, $deploymentMode);
        $risks = $this->riskAnalyzer->analyze($deploymentMode, in_array($deploymentMode, ['private_cloud', 'sovereign_self_hosted'], true));

        $proposal = IntelligenceProposal::query()->create([
            'intelligence_tenant_id' => $tenant->id,
            'intelligence_package_id' => $package->id,
            'stage' => 'draft',
            'title' => $tenant->name.' commercial proposal',
            'summary' => 'Structured commercialization payload for enterprise intelligence deployment.',
            'currency' => 'ZAR',
            'estimated_monthly_value' => $pricing['monthly_estimate'],
            'payload' => [],
            'metadata' => ['deployment_mode' => $deploymentMode],
        ]);

        ProposalPackageLine::query()->create([
            'intelligence_proposal_id' => $proposal->id,
            'line_label' => $package->name,
            'quantity' => 1,
            'unit_price' => $pricing['base_price'],
            'line_total' => $pricing['base_price'],
            'metadata' => [],
        ]);

        ProposalDeploymentLine::query()->create([
            'intelligence_proposal_id' => $proposal->id,
            'line_label' => 'Deployment posture',
            'deployment_mode' => $deploymentMode,
            'line_total' => $pricing['monthly_estimate'] - $pricing['base_price'],
            'metadata' => [],
        ]);

        ProposalServiceLine::query()->create([
            'intelligence_proposal_id' => $proposal->id,
            'line_label' => 'Commercial support and onboarding',
            'service_type' => 'onboarding',
            'quantity' => 1,
            'unit_price' => 15000,
            'line_total' => 15000,
            'metadata' => [],
        ]);

        ProposalAssumption::query()->create([
            'intelligence_proposal_id' => $proposal->id,
            'assumption' => 'Client provides timely access to required stakeholders and environments.',
            'status' => 'active',
            'metadata' => [],
        ]);

        foreach ($risks as $risk) {
            ProposalRisk::query()->create([
                'intelligence_proposal_id' => $proposal->id,
                'risk' => $risk['risk'],
                'severity' => $risk['severity'],
                'mitigation' => 'Coordinate through commercial and deployment readiness gates.',
                'metadata' => [],
            ]);
        }

        $payload = new ProposalPayloadData(
            packageLines: $proposal->packageLines()->get()->toArray(),
            deploymentLines: $proposal->deploymentLines()->get()->toArray(),
            serviceLines: $proposal->serviceLines()->get()->toArray(),
            assumptions: $proposal->assumptions()->get()->toArray(),
            risks: $proposal->risks()->get()->toArray(),
        );

        $proposal->forceFill(['payload' => $payload->toArray()])->save();

        return $proposal->fresh(['packageLines', 'deploymentLines', 'serviceLines', 'assumptions', 'risks']);
    }
}