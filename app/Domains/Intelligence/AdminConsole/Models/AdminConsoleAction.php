<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\AdminConsole\Models;

use App\Domains\Intelligence\AdminConsole\Enums\AdminConsoleActionStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminConsoleAction extends AdminConsoleRecord
{
    protected $fillable = [
        'uuid',
        'action_type',
        'title',
        'description',
        'target_context',
        'target_type',
        'target_id',
        'status',
        'requested_by',
        'approved_by',
        'executed_by',
        'payload',
        'result',
        'requested_at',
        'approved_at',
        'executed_at',
        'failed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => AdminConsoleActionStatus::class,
            'payload' => 'array',
            'result' => 'array',
            'requested_at' => 'datetime',
            'approved_at' => 'datetime',
            'executed_at' => 'datetime',
            'failed_at' => 'datetime',
        ];
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereNotIn('status', [
            AdminConsoleActionStatus::Completed->value,
            AdminConsoleActionStatus::Failed->value,
            AdminConsoleActionStatus::Cancelled->value,
            AdminConsoleActionStatus::Rejected->value,
        ]);
    }

    public function scopeRecent(Builder $query): Builder
    {
        return $query->latest('requested_at')->latest('created_at');
    }

    public function requestedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function executedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'executed_by');
    }
}
