<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Operations\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MissionMetric extends OperationsRecord
{
    protected $fillable = [
        'enterprise_mission_id',
        'metric_key',
        'metric_value',
        'unit',
        'recorded_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metric_value' => 'decimal:4',
            'recorded_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function mission(): BelongsTo
    {
        return $this->belongsTo(EnterpriseMission::class, 'enterprise_mission_id');
    }
}
