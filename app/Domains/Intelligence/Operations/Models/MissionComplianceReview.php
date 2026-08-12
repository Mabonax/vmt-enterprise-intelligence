<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Operations\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MissionComplianceReview extends OperationsRecord
{
    protected $fillable = [
        'enterprise_mission_id',
        'framework',
        'status',
        'compliance_score',
        'findings',
        'remediation_actions',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'compliance_score' => 'decimal:2',
            'findings' => 'array',
            'remediation_actions' => 'array',
            'metadata' => 'array',
        ];
    }

    public function mission(): BelongsTo
    {
        return $this->belongsTo(EnterpriseMission::class, 'enterprise_mission_id');
    }
}
