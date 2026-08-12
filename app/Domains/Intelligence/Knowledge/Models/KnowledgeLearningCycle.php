<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Knowledge\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class KnowledgeLearningCycle extends Model
{
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'workspace',
        'status',
        'outcome',
        'confidence',
        'latency_ms',
        'tools_used',
        'verification_results',
        'planner_decisions',
        'feedback_summary',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'tools_used' => 'array',
            'verification_results' => 'array',
            'planner_decisions' => 'array',
            'metadata' => 'array',
        ];
    }
}
