<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\AdminConsole\Services;

use App\Domains\Intelligence\AdminConsole\DTOs\AdminConsoleDashboardData;
use App\Domains\Intelligence\AdminConsole\Enums\AdminConsoleAudienceType;
use App\Domains\Intelligence\AdminConsole\Models\AdminConsoleAlert;
use App\Domains\Intelligence\AdminConsole\Models\AdminConsoleAction;
use App\Domains\Intelligence\AdminConsole\Models\AdminConsoleDashboard;
use App\Domains\Intelligence\AdminConsole\Models\AdminConsoleWidget;
use App\Domains\Intelligence\AdminConsole\Repositories\AdminConsoleActionRepository;
use App\Domains\Intelligence\AdminConsole\Repositories\AdminConsoleAlertRepository;
use App\Domains\Intelligence\AdminConsole\Repositories\AdminConsoleDashboardRepository;

class AdminConsoleDashboardService
{
    public function __construct(
        private readonly AdminConsoleDashboardRepository $dashboards,
        private readonly AdminConsoleMetricsService $metrics,
        private readonly AdminConsoleAlertRepository $alerts,
        private readonly AdminConsoleActionRepository $actions,
        private readonly AdminConsoleHealthService $health,
        private readonly AdminConsoleReadinessService $readiness,
    ) {}

    public function build(string $slug, AdminConsoleAudienceType $audience): AdminConsoleDashboardData
    {
        $dashboard = $this->dashboards->forSlug($slug)
            ?? AdminConsoleDashboard::query()->active()->where('audience_type', $audience->value)->where('is_default', true)->with(['widgets' => fn ($query) => $query->active()])->first()
            ?? new AdminConsoleDashboard([
                'name' => ucfirst($audience->value).' Dashboard',
                'slug' => $slug,
                'description' => 'Generated dashboard payload.',
                'audience_type' => $audience,
                'layout_config' => [],
                'widget_config' => [],
            ]);

        return new AdminConsoleDashboardData(
            dashboard: [
                'name' => $dashboard->name,
                'slug' => $dashboard->slug,
                'description' => $dashboard->description,
                'audience_type' => $dashboard->audience_type instanceof AdminConsoleAudienceType ? $dashboard->audience_type->value : $audience->value,
                'layout_config' => $dashboard->layout_config ?? [],
                'widget_config' => $dashboard->widget_config ?? [],
            ],
            metrics: $this->metrics->summary(),
            widgets: $dashboard->relationLoaded('widgets')
                ? $dashboard->widgets->map(fn (AdminConsoleWidget $widget): array => [
                    'name' => $widget->name,
                    'slug' => $widget->slug,
                    'widget_type' => $widget->widget_type->value,
                    'data_source' => $widget->data_source,
                    'display_config' => $widget->display_config ?? [],
                ])->all()
                : [],
            alerts: $this->alerts->unresolved()->map(fn (AdminConsoleAlert $alert): array => [
                'id' => $alert->id,
                'title' => $alert->title,
                'severity' => $alert->severity->value,
                'status' => $alert->status->value,
                'source_context' => $alert->source_context,
            ])->all(),
            actions: $this->actions->open()->map(fn (AdminConsoleAction $action): array => [
                'id' => $action->id,
                'title' => $action->title,
                'status' => $action->status->value,
                'action_type' => $action->action_type,
                'target_context' => $action->target_context,
            ])->all(),
            health: $this->health->latest(),
            readiness: $this->readiness->summary(),
        );
    }
}
