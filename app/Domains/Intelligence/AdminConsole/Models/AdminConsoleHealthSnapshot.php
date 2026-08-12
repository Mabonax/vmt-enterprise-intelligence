<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\AdminConsole\Models;

use App\Domains\Intelligence\AdminConsole\Enums\AdminConsoleHealthStatus;
use Illuminate\Database\Eloquent\Builder;

class AdminConsoleHealthSnapshot extends AdminConsoleRecord
{
    protected $fillable = [
        'uuid',
        'snapshot_type',
        'overall_status',
        'score',
        'signals',
        'recommendations',
        'captured_at',
    ];

    protected function casts(): array
    {
        return [
            'overall_status' => AdminConsoleHealthStatus::class,
            'score' => 'float',
            'signals' => 'array',
            'recommendations' => 'array',
            'captured_at' => 'datetime',
        ];
    }

    public function scopeRecent(Builder $query): Builder
    {
        return $query->latest('captured_at')->latest('created_at');
    }
}
