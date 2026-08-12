<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Operations\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MissionPolicy extends OperationsRecord
{
    protected $fillable = [
        'enterprise_mission_id',
        'policy_type',
        'status',
        'evaluation_score',
        'violations',
        'recommendations',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'evaluation_score' => 'decimal:2',
            'violations' => 'array',
            'recommendations' => 'array',
            'metadata' => 'array',
        ];
    }

    public function mission(): BelongsTo
    {
        return $this->belongsTo(EnterpriseMission::class, 'enterprise_mission_id');
    }
}
