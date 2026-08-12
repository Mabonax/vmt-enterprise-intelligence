<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Models;

use App\Domains\Intelligence\Enums\ExecutionStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AgentDelegation extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'execution_trace_id',
        'source_agent_id',
        'target_agent_id',
        'status',
        'objective',
        'shared_context',
        'result_payload',
    ];

    protected function casts(): array
    {
        return [
            'status' => ExecutionStatus::class,
            'shared_context' => 'array',
            'result_payload' => 'array',
        ];
    }
}
