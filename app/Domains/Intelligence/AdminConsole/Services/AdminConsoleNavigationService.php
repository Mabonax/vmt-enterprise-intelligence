<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\AdminConsole\Services;

final class AdminConsoleNavigationService
{
    public static function items(): array
    {
        return [
            ['label' => 'Command Center', 'route' => 'intelligence.admin-console.index', 'slug' => 'intelligence-admin-console-command-center', 'description' => 'Unified internal enterprise command center for operators, admins, and owners.', 'group' => 'intelligence'],
            ['label' => 'Executive View', 'route' => 'intelligence.admin-console.executive', 'slug' => 'intelligence-admin-console-executive', 'description' => 'Business posture, tenant growth, package distribution, and strategic risk visibility.', 'group' => 'intelligence'],
            ['label' => 'Operations View', 'route' => 'intelligence.admin-console.operations', 'slug' => 'intelligence-admin-console-operations', 'description' => 'Mission health, operational alerts, pending approvals, and execution control.', 'group' => 'intelligence'],
            ['label' => 'Commercial View', 'route' => 'intelligence.admin-console.commercial', 'slug' => 'intelligence-admin-console-commercial', 'description' => 'Packages, proposals, tenants, billing readiness, and onboarding oversight.', 'group' => 'intelligence'],
            ['label' => 'Technical View', 'route' => 'intelligence.admin-console.technical', 'slug' => 'intelligence-admin-console-technical', 'description' => 'Provider, agent, tooling, knowledge, deployment, and system signal visibility.', 'group' => 'intelligence'],
            ['label' => 'Support View', 'route' => 'intelligence.admin-console.support', 'slug' => 'intelligence-admin-console-support', 'description' => 'Queue pressure, open issues, tenant impact, and SLA-style support triage.', 'group' => 'intelligence'],
            ['label' => 'Compliance View', 'route' => 'intelligence.admin-console.compliance', 'slug' => 'intelligence-admin-console-compliance', 'description' => 'Policy warnings, approval gaps, audit visibility, and readiness blockers.', 'group' => 'intelligence'],
            ['label' => 'Alerts', 'route' => 'intelligence.admin-console.alerts', 'slug' => 'intelligence-admin-console-alerts', 'description' => 'Open, acknowledged, resolved, and dismissed command center alerts.', 'group' => 'intelligence'],
            ['label' => 'Action Queue', 'route' => 'intelligence.admin-console.actions', 'slug' => 'intelligence-admin-console-actions', 'description' => 'Operator action requests, approvals, execution, and internal results.', 'group' => 'intelligence'],
            ['label' => 'Audit Timeline', 'route' => 'intelligence.admin-console.audit', 'slug' => 'intelligence-admin-console-audit', 'description' => 'Searchable audit timeline for administrative state changes and responses.', 'group' => 'intelligence'],
            ['label' => 'Platform Health', 'route' => 'intelligence.admin-console.health', 'slug' => 'intelligence-admin-console-health', 'description' => 'Cross-domain health snapshots, scores, degraded areas, and recommendations.', 'group' => 'intelligence'],
            ['label' => 'Readiness', 'route' => 'intelligence.admin-console.readiness', 'slug' => 'intelligence-admin-console-readiness', 'description' => 'Commercial, deployment, support, compliance, and monitoring readiness posture.', 'group' => 'intelligence'],
            ['label' => 'Connected Applications', 'route' => 'intelligence.admin-console.connected-applications.index', 'slug' => 'intelligence-admin-console-connected-applications', 'description' => 'Register ERP clients, constrain gateway capabilities, and manage credentials.', 'group' => 'intelligence'],
        ];
    }
}
