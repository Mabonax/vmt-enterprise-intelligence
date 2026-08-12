<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\AdminConsole\Models;

use App\Domains\Intelligence\AdminConsole\Enums\AdminConsoleAlertSeverity;
use App\Domains\Intelligence\AdminConsole\Enums\AdminConsoleAlertStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminConsoleAlert extends AdminConsoleRecord
{
    protected $fillable = [
        'uuid',
        'alert_type',
        'severity',
        'title',
        'message',
        'source_context',
        'source_type',
        'source_id',
        'status',
        'assigned_to',
        'first_seen_at',
        'acknowledged_at',
        'resolved_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'severity' => AdminConsoleAlertSeverity::class,
            'status' => AdminConsoleAlertStatus::class,
            'first_seen_at' => 'datetime',
            'acknowledged_at' => 'datetime',
            'resolved_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', AdminConsoleAlertStatus::Open->value);
    }

    public function scopeUnresolved(Builder $query): Builder
    {
        return $query->whereNotIn('status', [
            AdminConsoleAlertStatus::Resolved->value,
            AdminConsoleAlertStatus::Dismissed->value,
        ]);
    }

    public function scopeCritical(Builder $query): Builder
    {
        return $query->whereIn('severity', [
            AdminConsoleAlertSeverity::Critical->value,
            AdminConsoleAlertSeverity::Blocker->value,
        ]);
    }

    public function scopeRecent(Builder $query): Builder
    {
        return $query->latest('first_seen_at')->latest('created_at');
    }

    public function assignedToUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
