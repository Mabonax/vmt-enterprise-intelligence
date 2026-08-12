<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Models;

use App\Domains\Intelligence\Enums\ExecutionStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $id
 */
class ExecutionPlan extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'conversation_id',
        'agent_id',
        'objective',
        'status',
        'estimated_complexity',
        'completion_state',
        'max_iterations',
        'attempts',
        'required_tools',
        'dependencies',
        'retry_strategy',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'status' => ExecutionStatus::class,
            'required_tools' => 'array',
            'dependencies' => 'array',
            'retry_strategy' => 'array',
            'metadata' => 'array',
        ];
    }

    public function steps(): HasMany
    {
        return $this->hasMany(ExecutionPlanStep::class);
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }
}
