<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Operations\Services;

use App\Domains\Intelligence\Operations\Models\EnterpriseMission;
use App\Domains\Intelligence\Operations\Models\MissionApproval;
use App\Models\User;

class ApprovalWorkflowService
{
    public function request(EnterpriseMission $mission, array $attributes = []): MissionApproval
    {
        return MissionApproval::query()->create([
            'enterprise_mission_id' => $mission->id,
            'approval_type' => $attributes['approval_type'] ?? 'sequential',
            'sequence_order' => (int) ($attributes['sequence_order'] ?? 1),
            'requested_role' => $attributes['requested_role'] ?? 'executive',
            'status' => 'pending',
            'delegated_to' => $attributes['delegated_to'] ?? null,
            'requested_by_user_id' => $attributes['requested_by_user_id'] ?? $mission->owner_user_id,
            'due_at' => $attributes['due_at'] ?? now()->addDay(),
            'metadata' => [
                'parallel' => ($attributes['approval_type'] ?? 'sequential') === 'parallel',
                'conditional' => ($attributes['approval_type'] ?? '') === 'conditional',
            ],
        ]);
    }

    public function approve(MissionApproval $approval, User $user, ?string $notes = null): MissionApproval
    {
        $approval->forceFill([
            'status' => 'approved',
            'approved_by_user_id' => $user->id,
            'decided_at' => now(),
            'decision_notes' => $notes,
            'signature_payload' => [
                'approved_by' => $user->email,
                'approved_at' => now()->toIso8601String(),
            ],
        ])->save();

        return $approval->fresh();
    }

    public function reject(MissionApproval $approval, User $user, ?string $notes = null): MissionApproval
    {
        $approval->forceFill([
            'status' => 'rejected',
            'approved_by_user_id' => $user->id,
            'decided_at' => now(),
            'decision_notes' => $notes,
        ])->save();

        return $approval->fresh();
    }
}
