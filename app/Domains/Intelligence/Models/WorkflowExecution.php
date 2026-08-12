<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Models;

use App\Domains\Intelligence\Enums\ExecutionStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkflowExecution extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'conversation_id',
        'agent_id',
        'name',
        'status',
        'steps',
        'rollback_payload',
        'approval_checkpoints',
        'execution_history',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'status' => ExecutionStatus::class,
            'steps' => 'array',
            'rollback_payload' => 'array',
            'approval_checkpoints' => 'array',
            'execution_history' => 'array',
            'metadata' => 'array',
        ];
    }
}
