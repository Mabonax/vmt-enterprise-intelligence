<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Operations\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MissionPlanVersion extends OperationsRecord
{
    protected $fillable = [
        'enterprise_mission_id',
        'version_number',
        'status',
        'complexity',
        'estimated_duration_hours',
        'estimated_financial_cost',
        'estimated_token_cost',
        'tool_requirements',
        'knowledge_requirements',
        'required_agent_skills',
        'confidence_score',
        'risk_score',
        'compliance_requirements',
        'dependency_map',
        'critical_path',
        'alternative_plans',
        'plan_payload',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'estimated_duration_hours' => 'decimal:2',
            'estimated_financial_cost' => 'decimal:2',
            'estimated_token_cost' => 'decimal:2',
            'tool_requirements' => 'array',
            'knowledge_requirements' => 'array',
            'required_agent_skills' => 'array',
            'confidence_score' => 'decimal:2',
            'risk_score' => 'decimal:2',
            'compliance_requirements' => 'array',
            'dependency_map' => 'array',
            'critical_path' => 'array',
            'alternative_plans' => 'array',
            'plan_payload' => 'array',
            'metadata' => 'array',
        ];
    }

    public function mission(): BelongsTo
    {
        return $this->belongsTo(EnterpriseMission::class, 'enterprise_mission_id');
    }
}
