<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Operations\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MissionDependency extends OperationsRecord
{
    protected $fillable = [
        'enterprise_mission_id',
        'dependency_type',
        'source_reference',
        'target_reference',
        'status',
        'metadata',
    ];

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }

    public function mission(): BelongsTo
    {
        return $this->belongsTo(EnterpriseMission::class, 'enterprise_mission_id');
    }
}
