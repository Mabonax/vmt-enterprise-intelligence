<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Commercial\Services;

use App\Domains\Intelligence\Commercial\Models\IntelligenceProposal;
use App\Domains\Intelligence\Commercial\Models\ProposalApproval;

class ProposalApprovalService
{
    public function moveToStage(IntelligenceProposal $proposal, string $stage): IntelligenceProposal
    {
        if ($stage === 'approved') {
            ProposalApproval::query()->create([
                'intelligence_proposal_id' => $proposal->id,
                'approver_name' => 'Commercial Director',
                'status' => 'approved',
                'notes' => 'Phase 9 approval workflow completed.',
                'approved_at' => now(),
                'metadata' => [],
            ]);
        }

        $proposal->forceFill([
            'stage' => $stage,
            'sent_at' => $stage === 'sent' ? now() : $proposal->sent_at,
            'accepted_at' => $stage === 'accepted' ? now() : $proposal->accepted_at,
        ])->save();

        return $proposal->fresh('approvals');
    }
}