<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Models;

use App\Domains\Intelligence\Enums\ExecutionStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $id
 * @property string|null $conversation_id
 * @property ExecutionStatus $status
 */
class ExecutionTrace extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'conversation_id',
        'agent_id',
        'execution_plan_id',
        'verification_log_id',
        'provider',
        'model',
        'completion_reason',
        'status',
        'iterations',
        'duration_ms',
        'input_tokens',
        'output_tokens',
        'plan_payload',
        'step_payloads',
        'memory_payload',
        'verification_payload',
        'trace_payload',
        'delegation_payload',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'status' => ExecutionStatus::class,
            'plan_payload' => 'array',
            'step_payloads' => 'array',
            'memory_payload' => 'array',
            'verification_payload' => 'array',
            'trace_payload' => 'array',
            'delegation_payload' => 'array',
            'metadata' => 'array',
        ];
    }

    public function toolLogs(): HasMany
    {
        return $this->hasMany(ToolExecutionLog::class);
    }
}
