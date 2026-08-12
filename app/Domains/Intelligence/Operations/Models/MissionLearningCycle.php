<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Operations\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MissionLearningCycle extends OperationsRecord
{
    protected $fillable = [
        'enterprise_mission_id',
        'learning_type',
        'status',
        'improvement_summary',
        'improvement_payload',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'improvement_payload' => 'array',
            'metadata' => 'array',
        ];
    }

    public function mission(): BelongsTo
    {
        return $this->belongsTo(EnterpriseMission::class, 'enterprise_mission_id');
    }
}
