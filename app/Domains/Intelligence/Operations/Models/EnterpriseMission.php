<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Operations\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EnterpriseMission extends OperationsRecord
{
    protected $fillable = [
        'owner_user_id',
        'mission_key',
        'title',
        'objective_summary',
        'priority',
        'status',
        'owner_name',
        'budget_amount',
        'budget_currency',
        'deadline_at',
        'estimated_completion_at',
        'started_at',
        'completed_at',
        'completion_percentage',
        'health_score',
        'risk_score',
        'mission_payload',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'budget_amount' => 'decimal:2',
            'deadline_at' => 'datetime',
            'estimated_completion_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'completion_percentage' => 'decimal:2',
            'health_score' => 'decimal:2',
            'risk_score' => 'decimal:2',
            'mission_payload' => 'array',
            'metadata' => 'array',
        ];
    }

    public function objectives(): HasMany
    {
        return $this->hasMany(MissionObjective::class);
    }

    public function phases(): HasMany
    {
        return $this->hasMany(MissionPhase::class);
    }

    public function executions(): HasMany
    {
        return $this->hasMany(MissionExecution::class);
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(MissionMilestone::class);
    }

    public function checkpoints(): HasMany
    {
        return $this->hasMany(MissionCheckpoint::class);
    }

    public function dependencies(): HasMany
    {
        return $this->hasMany(MissionDependency::class);
    }

    public function outcomes(): HasMany
    {
        return $this->hasMany(MissionOutcome::class);
    }

    public function risks(): HasMany
    {
        return $this->hasMany(MissionRisk::class);
    }

    public function metrics(): HasMany
    {
        return $this->hasMany(MissionMetric::class);
    }

    public function planVersions(): HasMany
    {
        return $this->hasMany(MissionPlanVersion::class);
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(MissionApproval::class);
    }

    public function decisions(): HasMany
    {
        return $this->hasMany(MissionDecisionRecord::class);
    }

    public function predictions(): HasMany
    {
        return $this->hasMany(MissionPrediction::class);
    }

    public function simulations(): HasMany
    {
        return $this->hasMany(ScenarioSimulation::class);
    }

    public function policyEvaluations(): HasMany
    {
        return $this->hasMany(MissionPolicy::class);
    }

    public function complianceReviews(): HasMany
    {
        return $this->hasMany(MissionComplianceReview::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'owner_user_id');
    }
}
