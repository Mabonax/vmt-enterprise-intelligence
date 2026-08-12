<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Models;

use App\Domains\Intelligence\Enums\AgentStatus;
use App\Domains\Intelligence\Enums\AgentVisibility;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $id
 * @property int|null $owner_user_id
 * @property string|null $organization_id
 * @property string $name
 * @property string $slug
 * @property string|null $system_instructions
 * @property string|null $default_provider
 * @property string|null $default_model
 * @property string|null $agent_role_key
 * @property string $reasoning_style
 * @property string $risk_tolerance
 * @property string $verification_strategy
 * @property string $memory_scope
 * @property bool $delegation_enabled
 * @property bool $approval_required
 * @property array<int, string>|null $allowed_tools
 * @property bool $memory_enabled
 * @property AgentVisibility $visibility
 * @method static \Illuminate\Database\Eloquent\Builder<self> query()
 */
class Agent extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'owner_user_id',
        'organization_id',
        'name',
        'slug',
        'agent_role_key',
        'description',
        'status',
        'purpose',
        'system_instructions',
        'default_provider',
        'default_model',
        'reasoning_style',
        'risk_tolerance',
        'verification_strategy',
        'memory_scope',
        'temperature',
        'max_tokens',
        'allowed_tools',
        'allowed_knowledge_sources',
        'memory_enabled',
        'delegation_enabled',
        'approval_required',
        'conversation_limit',
        'visibility',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'status' => AgentStatus::class,
            'visibility' => AgentVisibility::class,
            'temperature' => 'float',
            'memory_enabled' => 'bool',
            'delegation_enabled' => 'bool',
            'approval_required' => 'bool',
            'allowed_tools' => 'array',
            'allowed_knowledge_sources' => 'array',
            'metadata' => 'array',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }
}
