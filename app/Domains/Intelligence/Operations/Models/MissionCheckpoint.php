<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Operations\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MissionCheckpoint extends OperationsRecord
{
    protected $fillable = [
        'enterprise_mission_id',
        'mission_execution_id',
        'checkpoint_type',
        'status',
        'snapshot_payload',
        'recorded_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'snapshot_payload' => 'array',
            'recorded_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function mission(): BelongsTo
    {
        return $this->belongsTo(EnterpriseMission::class, 'enterprise_mission_id');
    }

    public function execution(): BelongsTo
    {
        return $this->belongsTo(MissionExecution::class, 'mission_execution_id');
    }
}
