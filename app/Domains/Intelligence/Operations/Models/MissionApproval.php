<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Operations\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MissionApproval extends OperationsRecord
{
    protected $fillable = [
        'enterprise_mission_id',
        'approval_type',
        'sequence_order',
        'requested_role',
        'status',
        'delegated_to',
        'requested_by_user_id',
        'approved_by_user_id',
        'due_at',
        'decided_at',
        'decision_notes',
        'signature_payload',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'due_at' => 'datetime',
            'decided_at' => 'datetime',
            'signature_payload' => 'array',
            'metadata' => 'array',
        ];
    }

    public function mission(): BelongsTo
    {
        return $this->belongsTo(EnterpriseMission::class, 'enterprise_mission_id');
    }
}
