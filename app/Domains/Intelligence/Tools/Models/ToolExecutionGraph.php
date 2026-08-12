<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Tools\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ToolExecutionGraph extends Model
{
    use HasUuids;

    protected $table = 'tool_execution_graph';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'execution_trace_id',
        'tool_execution_id',
        'parent_tool_execution_id',
        'node_key',
        'depth',
        'metadata',
    ];

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }
}
