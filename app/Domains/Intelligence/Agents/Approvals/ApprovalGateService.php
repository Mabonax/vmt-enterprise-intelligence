<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Agents\Approvals;

use App\Domains\Intelligence\Agents\Models\AgentApproval;
use App\Domains\Intelligence\Agents\Models\AgentSession;
use App\Models\User;

class ApprovalGateService
{
    public function request(AgentSession $session, ?string $workflowId, User $user, string $approvalType, ?string $requestedRole, string $reason): AgentApproval
    {
        return AgentApproval::query()->create([
            'agent_session_id' => $session->id,
            'agent_workflow_id' => $workflowId,
            'approval_type' => $approvalType,
            'requested_role' => $requestedRole,
            'requested_by_user_id' => $user->id,
            'status' => 'pending',
            'reason' => $reason,
        ]);
    }

    public function approve(AgentApproval $approval, User $user, ?string $notes = null): AgentApproval
    {
        $approval->forceFill([
            'status' => 'approved',
            'approved_by_user_id' => $user->id,
            'decision_notes' => $notes,
            'decided_at' => now(),
        ])->save();

        return $approval->refresh();
    }

    public function reject(AgentApproval $approval, User $user, ?string $notes = null): AgentApproval
    {
        $approval->forceFill([
            'status' => 'rejected',
            'approved_by_user_id' => $user->id,
            'decision_notes' => $notes,
            'decided_at' => now(),
        ])->save();

        return $approval->refresh();
    }
}
