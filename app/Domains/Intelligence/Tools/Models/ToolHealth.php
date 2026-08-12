<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Tools\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ToolHealth extends Model
{
    use HasUuids;

    protected $table = 'tool_health';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'enterprise_tool_id',
        'availability',
        'latency_ms',
        'timeouts',
        'errors',
        'average_duration_ms',
        'success_rate',
        'last_execution_at',
        'last_failure_at',
        'health_score',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'availability' => 'bool',
            'success_rate' => 'float',
            'health_score' => 'float',
            'last_execution_at' => 'datetime',
            'last_failure_at' => 'datetime',
            'metadata' => 'array',
        ];
    }
}
