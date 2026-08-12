<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Models;

use App\Domains\Intelligence\Enums\ExecutionStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $id
 * @property int $sequence
 * @property string $title
 * @property string|null $tool_slug
 * @property int $attempts
 * @method static \Illuminate\Database\Eloquent\Builder<self> query()
 */
class ExecutionPlanStep extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'execution_plan_id',
        'workflow_execution_id',
        'sequence',
        'title',
        'tool_slug',
        'step_kind',
        'status',
        'dependencies',
        'required_tools',
        'verification_requirements',
        'input_payload',
        'output_payload',
        'retry_limit',
        'attempts',
        'verification_notes',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'status' => ExecutionStatus::class,
            'dependencies' => 'array',
            'required_tools' => 'array',
            'verification_requirements' => 'array',
            'input_payload' => 'array',
            'output_payload' => 'array',
            'metadata' => 'array',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(ExecutionPlan::class, 'execution_plan_id');
    }
}
