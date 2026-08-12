<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Operations\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MissionObjective extends OperationsRecord
{
    protected $fillable = [
        'enterprise_mission_id',
        'title',
        'description',
        'objective_type',
        'priority',
        'status',
        'sequence',
        'completion_percentage',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'completion_percentage' => 'decimal:2',
            'metadata' => 'array',
        ];
    }

    public function mission(): BelongsTo
    {
        return $this->belongsTo(EnterpriseMission::class, 'enterprise_mission_id');
    }
}
