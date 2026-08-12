<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\AdminConsole\Models;

use App\Domains\Intelligence\AdminConsole\Enums\AdminConsoleAudienceType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdminConsoleDashboard extends AdminConsoleRecord
{
    protected $fillable = [
        'uuid',
        'name',
        'slug',
        'description',
        'audience_type',
        'layout_config',
        'widget_config',
        'is_default',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'audience_type' => AdminConsoleAudienceType::class,
            'layout_config' => 'array',
            'widget_config' => 'array',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function widgets(): HasMany
    {
        return $this->hasMany(AdminConsoleWidget::class, 'dashboard_id')->orderBy('position');
    }
}
