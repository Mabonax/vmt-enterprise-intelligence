<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domains\Intelligence\AdminConsole\Enums\AdminConsoleAlertSeverity;
use App\Domains\Intelligence\AdminConsole\Enums\AdminConsoleAudienceType;
use App\Domains\Intelligence\AdminConsole\Enums\AdminConsoleWidgetType;
use App\Domains\Intelligence\AdminConsole\Models\AdminConsoleDashboard;
use App\Domains\Intelligence\AdminConsole\Models\AdminConsoleWidget;
use App\Domains\Intelligence\AdminConsole\Services\AdminConsoleAlertService;
use App\Domains\Intelligence\AdminConsole\Services\AdminConsoleHealthService;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class IntelligenceAdminConsoleSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'intelligence.admin_console.view',
            'intelligence.admin_console.manage',
            'intelligence.admin_console.alerts.manage',
            'intelligence.admin_console.actions.manage',
            'intelligence.admin_console.audit.view',
            'intelligence.admin_console.health.view',
            'intelligence.admin_console.readiness.view',
        ] as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $administrator = User::query()->where('email', env('VIP_ADMIN_EMAIL', 'admin@vip.local'))->first();

        if ($administrator !== null) {
            $administrator->givePermissionTo([
                'intelligence.admin_console.view',
                'intelligence.admin_console.manage',
                'intelligence.admin_console.alerts.manage',
                'intelligence.admin_console.actions.manage',
                'intelligence.admin_console.audit.view',
                'intelligence.admin_console.health.view',
                'intelligence.admin_console.readiness.view',
            ]);
        }

        $dashboards = collect([
            ['name' => 'Command Center Dashboard', 'slug' => 'index', 'audience' => AdminConsoleAudienceType::Operator, 'default' => true],
            ['name' => 'Executive Dashboard', 'slug' => 'executive', 'audience' => AdminConsoleAudienceType::Executive, 'default' => true],
            ['name' => 'Operations Dashboard', 'slug' => 'operations', 'audience' => AdminConsoleAudienceType::Operator, 'default' => false],
            ['name' => 'Commercial Dashboard', 'slug' => 'commercial', 'audience' => AdminConsoleAudienceType::Commercial, 'default' => false],
            ['name' => 'Technical Dashboard', 'slug' => 'technical', 'audience' => AdminConsoleAudienceType::Technical, 'default' => false],
            ['name' => 'Support Dashboard', 'slug' => 'support', 'audience' => AdminConsoleAudienceType::Support, 'default' => false],
            ['name' => 'Compliance Dashboard', 'slug' => 'compliance', 'audience' => AdminConsoleAudienceType::Compliance, 'default' => false],
        ])->map(function (array $definition): AdminConsoleDashboard {
            return AdminConsoleDashboard::query()->updateOrCreate(
                ['slug' => $definition['slug']],
                [
                    'uuid' => (string) str()->uuid(),
                    'name' => $definition['name'],
                    'description' => $definition['name'].' for internal enterprise oversight.',
                    'audience_type' => $definition['audience']->value,
                    'layout_config' => ['columns' => 2],
                    'widget_config' => ['density' => 'comfortable'],
                    'is_default' => $definition['default'],
                    'is_active' => true,
                ],
            );
        });

        $widgetDefinitions = [
            ['slug' => 'command-center-status', 'name' => 'Command Center Status', 'type' => AdminConsoleWidgetType::Metric, 'source' => 'metrics.summary', 'dashboard' => 'index'],
            ['slug' => 'executive-growth', 'name' => 'Executive Growth', 'type' => AdminConsoleWidgetType::Chart, 'source' => 'metrics.executive', 'dashboard' => 'executive'],
            ['slug' => 'operations-alerts', 'name' => 'Operational Alerts', 'type' => AdminConsoleWidgetType::AlertList, 'source' => 'alerts.feed', 'dashboard' => 'operations'],
            ['slug' => 'commercial-pipeline', 'name' => 'Commercial Pipeline', 'type' => AdminConsoleWidgetType::Table, 'source' => 'commercial.pipeline', 'dashboard' => 'commercial'],
            ['slug' => 'technical-health', 'name' => 'Technical Health', 'type' => AdminConsoleWidgetType::HealthPanel, 'source' => 'health.latest', 'dashboard' => 'technical'],
            ['slug' => 'support-queue', 'name' => 'Support Queue', 'type' => AdminConsoleWidgetType::Table, 'source' => 'support.queue', 'dashboard' => 'support'],
            ['slug' => 'compliance-readiness', 'name' => 'Compliance Readiness', 'type' => AdminConsoleWidgetType::ReadinessPanel, 'source' => 'readiness.summary', 'dashboard' => 'compliance'],
        ];

        foreach ($widgetDefinitions as $index => $definition) {
            $dashboard = $dashboards->firstWhere('slug', $definition['dashboard']);

            AdminConsoleWidget::query()->updateOrCreate(
                ['slug' => $definition['slug']],
                [
                    'uuid' => (string) str()->uuid(),
                    'dashboard_id' => $dashboard?->id,
                    'name' => $definition['name'],
                    'widget_type' => $definition['type']->value,
                    'data_source' => $definition['source'],
                    'refresh_interval_seconds' => 300,
                    'query_config' => [],
                    'display_config' => ['style' => 'compact'],
                    'position' => $index,
                    'is_active' => true,
                ],
            );
        }

        if (app()->environment(['local', 'testing'])) {
            app(AdminConsoleAlertService::class)->create([
                'alert_type' => 'quota_pressure',
                'severity' => AdminConsoleAlertSeverity::Warning,
                'title' => 'Usage quota nearing warning threshold',
                'message' => 'One or more subscriptions are approaching soft usage thresholds.',
                'source_context' => 'commercial',
                'source_type' => 'subscription',
                'source_id' => 'sample-subscription',
                'metadata' => ['sample' => true],
            ]);

            app(AdminConsoleAlertService::class)->create([
                'alert_type' => 'support_backlog',
                'severity' => AdminConsoleAlertSeverity::Info,
                'title' => 'Support queue requires review',
                'message' => 'Low-severity queue review recommended for support operations.',
                'source_context' => 'support',
                'source_type' => 'ticket_queue',
                'source_id' => 'support-default',
                'metadata' => ['sample' => true],
            ]);
        }

        app(AdminConsoleHealthService::class)->capture('seed');
    }
}
