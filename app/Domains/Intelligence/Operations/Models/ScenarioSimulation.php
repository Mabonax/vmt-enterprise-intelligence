<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Operations\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScenarioSimulation extends OperationsRecord
{
    protected $fillable = [
        'enterprise_mission_id',
        'scenario_type',
        'scenario_label',
        'status',
        'predicted_outcome',
        'impact_summary',
        'simulation_payload',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'predicted_outcome' => 'array',
            'impact_summary' => 'array',
            'simulation_payload' => 'array',
            'metadata' => 'array',
        ];
    }

    public function mission(): BelongsTo
    {
        return $this->belongsTo(EnterpriseMission::class, 'enterprise_mission_id');
    }
}
