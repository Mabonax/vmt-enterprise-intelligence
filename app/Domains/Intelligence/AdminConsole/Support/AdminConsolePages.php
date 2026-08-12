<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\AdminConsole\Support;

use App\Domains\Intelligence\AdminConsole\Enums\AdminConsoleAudienceType;

final class AdminConsolePages
{
    public static function definitions(): array
    {
        return [
            'index' => ['title' => 'Command Center', 'route' => 'intelligence.admin-console.index', 'slug' => 'admin-console-command-center', 'audience' => AdminConsoleAudienceType::Operator, 'description' => 'Unified platform posture across tenants, deployments, support, health, and actions.'],
            'executive' => ['title' => 'Executive View', 'route' => 'intelligence.admin-console.executive', 'slug' => 'admin-console-executive', 'audience' => AdminConsoleAudienceType::Executive, 'description' => 'Business-facing metrics, growth posture, readiness, and strategic risk visibility.'],
            'operations' => ['title' => 'Operations View', 'route' => 'intelligence.admin-console.operations', 'slug' => 'admin-console-operations', 'audience' => AdminConsoleAudienceType::Operator, 'description' => 'Mission health, alerts, approvals, and operational execution control.'],
            'commercial' => ['title' => 'Commercial View', 'route' => 'intelligence.admin-console.commercial', 'slug' => 'admin-console-commercial', 'audience' => AdminConsoleAudienceType::Commercial, 'description' => 'Package, tenant, proposal, subscription, and deployment readiness oversight.'],
            'technical' => ['title' => 'Technical View', 'route' => 'intelligence.admin-console.technical', 'slug' => 'admin-console-technical', 'audience' => AdminConsoleAudienceType::Technical, 'description' => 'Tools, connectors, agent runtime, knowledge ingestion, and deployment health signals.'],
            'support' => ['title' => 'Support View', 'route' => 'intelligence.admin-console.support', 'slug' => 'admin-console-support', 'audience' => AdminConsoleAudienceType::Support, 'description' => 'Queues, unresolved issues, SLA pressure, and tenant-impact triage visibility.'],
            'compliance' => ['title' => 'Compliance View', 'route' => 'intelligence.admin-console.compliance', 'slug' => 'admin-console-compliance', 'audience' => AdminConsoleAudienceType::Compliance, 'description' => 'Policy warnings, audit timelines, approval gaps, and readiness blockers.'],
        ];
    }
}
