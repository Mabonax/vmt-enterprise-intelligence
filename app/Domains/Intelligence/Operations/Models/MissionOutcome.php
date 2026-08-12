<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Operations\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MissionOutcome extends OperationsRecord
{
    protected $fillable = [
        'enterprise_mission_id',
        'outcome_type',
        'status',
        'summary',
        'verification_score',
        'confidence_score',
        'payload',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'verification_score' => 'decimal:2',
            'confidence_score' => 'decimal:2',
            'payload' => 'array',
            'metadata' => 'array',
        ];
    }

    public function mission(): BelongsTo
    {
        return $this->belongsTo(EnterpriseMission::class, 'enterprise_mission_id');
    }
}
