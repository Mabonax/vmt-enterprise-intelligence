<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Commercial\Actions;

use App\Domains\Intelligence\Commercial\Models\IntelligenceProposal;
use App\Domains\Intelligence\Commercial\Services\ProposalApprovalService;

class AdvanceProposalStageAction
{
    public function __construct(private readonly ProposalApprovalService $approvals) {}

    public function execute(IntelligenceProposal $proposal, string $stage): IntelligenceProposal
    {
        return $this->approvals->moveToStage($proposal, $stage);
    }
}