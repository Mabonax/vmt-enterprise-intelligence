<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\AdminConsole\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminConsoleAuditEvent extends AdminConsoleRecord
{
    protected $fillable = [
        'uuid',
        'actor_id',
        'event_type',
        'event_name',
        'source_context',
        'target_type',
        'target_id',
        'ip_address',
        'user_agent',
        'before_state',
        'after_state',
        'metadata',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'before_state' => 'array',
            'after_state' => 'array',
            'metadata' => 'array',
            'occurred_at' => 'datetime',
        ];
    }

    public function scopeRecent(Builder $query): Builder
    {
        return $query->latest('occurred_at')->latest('created_at');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
