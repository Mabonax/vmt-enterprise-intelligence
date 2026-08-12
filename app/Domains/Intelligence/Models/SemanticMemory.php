<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Models;

use App\Domains\Intelligence\Enums\MemoryType;
use App\Domains\Intelligence\Enums\MemoryVisibility;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SemanticMemory extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'created_by',
        'approved_by',
        'organization_id',
        'agent_id',
        'conversation_id',
        'conversation_message_id',
        'subject_type',
        'subject_id',
        'memory_type',
        'visibility',
        'content',
        'importance_score',
        'confidence_score',
        'expires_at',
        'reviewed_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'memory_type' => MemoryType::class,
            'visibility' => MemoryVisibility::class,
            'confidence_score' => 'float',
            'metadata' => 'array',
            'expires_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
