<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Models;

use App\Domains\Intelligence\Enums\ConversationStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $id
 * @property int $user_id
 * @property string|null $agent_id
 * @property string|null $prompt_template_id
 * @property string $title
 * @property string $provider
 * @property string $model
 */
class Conversation extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'title',
        'user_id',
        'agent_id',
        'prompt_template_id',
        'provider',
        'model',
        'status',
        'conversation_limit',
        'metadata',
        'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'archived_at' => 'datetime',
            'status' => ConversationStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ConversationMessage::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ConversationAttachment::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }
}
