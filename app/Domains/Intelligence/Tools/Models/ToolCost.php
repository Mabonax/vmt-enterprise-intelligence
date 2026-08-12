<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Tools\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ToolCost extends Model
{
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'enterprise_tool_id',
        'execution_trace_id',
        'api_usage_cost',
        'llm_token_cost',
        'storage_cost',
        'bandwidth_cost',
        'estimated_cost',
        'credits_consumed',
        'metadata',
    ];

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }
}
