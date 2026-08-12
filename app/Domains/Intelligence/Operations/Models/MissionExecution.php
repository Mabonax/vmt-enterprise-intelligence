<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Operations\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MissionExecution extends OperationsRecord
{
    protected $fillable = [
        'enterprise_mission_id',
        'execution_key',
        'status',
        'execution_mode',
        'assigned_team',
        'assigned_agents',
        'snapshot_payload',
        'result_payload',
        'retry_count',
        'started_at',
        'last_checkpoint_at',
        'completed_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'assigned_agents' => 'array',
            'snapshot_payload' => 'array',
            'result_payload' => 'array',
            'started_at' => 'datetime',
            'last_checkpoint_at' => 'datetime',
            'completed_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function mission(): BelongsTo
    {
        return $this->belongsTo(EnterpriseMission::class, 'enterprise_mission_id');
    }
}
