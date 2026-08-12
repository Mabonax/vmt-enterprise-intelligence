<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Tools\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ToolExecution extends Model
{
    use HasUuids;

    protected $table = 'enterprise_tool_executions';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'enterprise_tool_id',
        'execution_trace_id',
        'parent_execution_id',
        'status',
        'version',
        'connector_type',
        'input_payload',
        'output_payload',
        'replay_payload',
        'error_message',
        'duration_ms',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'input_payload' => 'array',
            'output_payload' => 'array',
            'replay_payload' => 'array',
            'metadata' => 'array',
        ];
    }
}
