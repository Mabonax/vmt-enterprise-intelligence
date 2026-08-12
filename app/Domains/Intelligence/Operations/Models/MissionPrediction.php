<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Operations\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MissionPrediction extends OperationsRecord
{
    protected $fillable = [
        'enterprise_mission_id',
        'prediction_type',
        'prediction_window',
        'confidence_score',
        'predicted_value',
        'recommendations',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'confidence_score' => 'decimal:2',
            'predicted_value' => 'decimal:2',
            'recommendations' => 'array',
            'metadata' => 'array',
        ];
    }

    public function mission(): BelongsTo
    {
        return $this->belongsTo(EnterpriseMission::class, 'enterprise_mission_id');
    }
}
