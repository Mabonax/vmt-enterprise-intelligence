<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Operations\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MissionDecisionRecord extends OperationsRecord
{
    protected $fillable = [
        'enterprise_mission_id',
        'decision_key',
        'context',
        'alternatives',
        'reasoning',
        'evidence',
        'confidence_score',
        'chosen_option',
        'approvals',
        'outcome',
        'lessons_learned',
        'review_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'context' => 'array',
            'alternatives' => 'array',
            'evidence' => 'array',
            'confidence_score' => 'decimal:2',
            'approvals' => 'array',
            'review_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function mission(): BelongsTo
    {
        return $this->belongsTo(EnterpriseMission::class, 'enterprise_mission_id');
    }
}
