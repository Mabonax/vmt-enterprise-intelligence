<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Models;

use App\Domains\Intelligence\Enums\ChatRole;
use App\Domains\Intelligence\Enums\MessageType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $id
 * @property string $conversation_id
 * @property string $content
 */
class ConversationMessage extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'conversation_id',
        'role',
        'type',
        'content',
        'citations',
        'tool_calls',
        'input_tokens',
        'output_tokens',
        'latency_ms',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'citations' => 'array',
            'tool_calls' => 'array',
            'metadata' => 'array',
            'role' => ChatRole::class,
            'type' => MessageType::class,
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }
}
