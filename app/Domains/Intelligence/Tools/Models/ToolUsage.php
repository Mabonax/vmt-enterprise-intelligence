<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Tools\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ToolUsage extends Model
{
    use HasUuids;

    protected $table = 'tool_usage';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'enterprise_tool_id',
        'user_id',
        'execution_trace_id',
        'success',
        'duration_ms',
        'tokens',
        'bandwidth_bytes',
        'storage_bytes',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'success' => 'bool',
            'metadata' => 'array',
        ];
    }
}
