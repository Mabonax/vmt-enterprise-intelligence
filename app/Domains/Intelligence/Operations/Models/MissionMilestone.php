<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Operations\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MissionMilestone extends OperationsRecord
{
    protected $fillable = [
        'enterprise_mission_id',
        'title',
        'status',
        'target_at',
        'completed_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'target_at' => 'datetime',
            'completed_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function mission(): BelongsTo
    {
        return $this->belongsTo(EnterpriseMission::class, 'enterprise_mission_id');
    }
}
