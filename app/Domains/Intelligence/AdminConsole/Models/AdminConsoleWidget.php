<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\AdminConsole\Models;

use App\Domains\Intelligence\AdminConsole\Enums\AdminConsoleWidgetType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminConsoleWidget extends AdminConsoleRecord
{
    protected $fillable = [
        'uuid',
        'dashboard_id',
        'name',
        'slug',
        'widget_type',
        'data_source',
        'refresh_interval_seconds',
        'query_config',
        'display_config',
        'position',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'widget_type' => AdminConsoleWidgetType::class,
            'query_config' => 'array',
            'display_config' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function dashboard(): BelongsTo
    {
        return $this->belongsTo(AdminConsoleDashboard::class, 'dashboard_id');
    }
}
