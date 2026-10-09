<?php

declare(strict_types=1);

namespace App\Support\Navigation;

use App\Domains\Intelligence\AdminConsole\Services\AdminConsoleNavigationService;

final class VipNavigation
{
    /**
     * @return list<array{label: string, route: string, slug: string, description: string, group: string}>
     */
    public static function items(): array
    {
        $items = [
            ['label' => 'Dashboard', 'route' => 'dashboard', 'slug' => 'dashboard', 'description' => 'Executive command overview for the platform foundation.', 'group' => 'platform'],
            ['label' => 'Intelligence Dashboard', 'route' => 'intelligence.dashboard', 'slug' => 'intelligence-dashboard', 'description' => 'Executive runtime metrics and additive scaffolding for the intelligence core.', 'group' => 'intelligence'],
            ['label' => 'Conversations', 'route' => 'intelligence.conversations', 'slug' => 'intelligence-conversations', 'description' => 'Provider-neutral conversation lifecycle and message persistence seams.', 'group' => 'intelligence'],
            ['label' => 'Providers', 'route' => 'intelligence.providers', 'slug' => 'intelligence-providers', 'description' => 'Stubbed provider registry and contract bindings for future integrations.', 'group' => 'intelligence'],
            ['label' => 'Agents', 'route' => 'intelligence.agents', 'slug' => 'intelligence-agents', 'description' => 'Agent contracts, memory strategy seams, and permission posture.', 'group' => 'intelligence'],
            ['label' => 'Tools', 'route' => 'intelligence.tools', 'slug' => 'intelligence-tools', 'description' => 'Callable tool framework and execution audit infrastructure.', 'group' => 'intelligence'],
            ['label' => 'Marketplace', 'route' => 'intelligence.marketplace', 'slug' => 'intelligence-marketplace', 'description' => 'Package publishing, installations, version control, and dependency visibility.', 'group' => 'intelligence'],
            ['label' => 'Connectors', 'route' => 'intelligence.connectors', 'slug' => 'intelligence-connectors', 'description' => 'Connector registry, driver health, and enterprise execution surfaces.', 'group' => 'intelligence'],
            ['label' => 'Credentials', 'route' => 'intelligence.credentials', 'slug' => 'intelligence-credentials', 'description' => 'Encrypted vault records for tool credentials and secret rotation posture.', 'group' => 'intelligence'],
            ['label' => 'Analytics', 'route' => 'intelligence.analytics', 'slug' => 'intelligence-analytics', 'description' => 'Tool adoption, runtime performance, and cost intelligence.', 'group' => 'intelligence'],
            ['label' => 'Health', 'route' => 'intelligence.health', 'slug' => 'intelligence-health', 'description' => 'Connector and tool health monitoring with additive scoring records.', 'group' => 'intelligence'],
            ['label' => 'Execution Graph', 'route' => 'intelligence.execution-graph', 'slug' => 'intelligence-execution-graph', 'description' => 'Replayable execution trees, graph nodes, and streaming checkpoints.', 'group' => 'intelligence'],
            ['label' => 'Execution History', 'route' => 'intelligence.execution-history', 'slug' => 'intelligence-execution-history', 'description' => 'Historical enterprise executions alongside legacy runtime logs.', 'group' => 'intelligence'],
            ['label' => 'Testing', 'route' => 'intelligence.testing', 'slug' => 'intelligence-testing', 'description' => 'Connector validation, replay, regression, and permission testing records.', 'group' => 'intelligence'],
            ['label' => 'SDK', 'route' => 'intelligence.sdk', 'slug' => 'intelligence-sdk', 'description' => 'SDK generation status and export tracking by language.', 'group' => 'intelligence'],
            ['label' => 'Costs', 'route' => 'intelligence.costs', 'slug' => 'intelligence-costs', 'description' => 'Execution cost accounting, credits consumed, and budget observability.', 'group' => 'intelligence'],
            ['label' => 'Knowledge Dashboard', 'route' => 'intelligence.knowledge-dashboard', 'slug' => 'intelligence-knowledge-dashboard', 'description' => 'Enterprise knowledge growth, memory signals, and retrieval readiness.', 'group' => 'intelligence'],
            ['label' => 'Enterprise Search', 'route' => 'intelligence.enterprise-search', 'slug' => 'intelligence-enterprise-search', 'description' => 'Hybrid retrieval across documents, memories, graph links, and runtime records.', 'group' => 'intelligence'],
            ['label' => 'Knowledge Graph', 'route' => 'intelligence.knowledge-graph', 'slug' => 'intelligence-knowledge-graph', 'description' => 'Graph nodes, edges, and relationship traversal across enterprise entities.', 'group' => 'intelligence'],
            ['label' => 'Memory Explorer', 'route' => 'intelligence.memory-explorer', 'slug' => 'intelligence-memory-explorer', 'description' => 'Persistent organizational memories with confidence, importance, and citations.', 'group' => 'intelligence'],
            ['label' => 'Document Library', 'route' => 'intelligence.document-library', 'slug' => 'intelligence-document-library', 'description' => 'Ingestion status, chunking, versions, and source-backed enterprise documents.', 'group' => 'intelligence'],
            ['label' => 'Collections', 'route' => 'intelligence.collections', 'slug' => 'intelligence-collections', 'description' => 'Workspace knowledge collections and source boundaries.', 'group' => 'intelligence'],
            ['label' => 'Embeddings', 'route' => 'intelligence.embeddings', 'slug' => 'intelligence-embeddings', 'description' => 'Embedding generation status, providers, and vector metadata.', 'group' => 'intelligence'],
            ['label' => 'Relationships', 'route' => 'intelligence.relationships', 'slug' => 'intelligence-relationships', 'description' => 'Discovered entity relationships and graph-linked enterprise dependencies.', 'group' => 'intelligence'],
            ['label' => 'Learning', 'route' => 'intelligence.learning', 'slug' => 'intelligence-learning', 'description' => 'Learning cycles, planner outcomes, and feedback-driven refinement signals.', 'group' => 'intelligence'],
            ['label' => 'Knowledge Timeline', 'route' => 'intelligence.knowledge-timeline', 'slug' => 'intelligence-knowledge-timeline', 'description' => 'Chronological knowledge events, ingestion, maintenance, and graph activity.', 'group' => 'intelligence'],
            ['label' => 'Knowledge Analytics', 'route' => 'intelligence.knowledge-analytics', 'slug' => 'intelligence-knowledge-analytics', 'description' => 'Knowledge growth, reuse, search latency, and learning effectiveness metrics.', 'group' => 'intelligence'],
            ['label' => 'Knowledge Health', 'route' => 'intelligence.knowledge-health', 'slug' => 'intelligence-knowledge-health', 'description' => 'Freshness, confidence, density, and semantic richness indicators.', 'group' => 'intelligence'],
            ['label' => 'Enterprise Agents', 'route' => 'intelligence.enterprise-agents', 'slug' => 'intelligence-enterprise-agents', 'description' => 'Multi-agent runtime orchestration, specialist roles, and execution readiness.', 'group' => 'intelligence'],
            ['label' => 'Agent Teams', 'route' => 'intelligence.agent-teams', 'slug' => 'intelligence-agent-teams', 'description' => 'Session teams, role specialization, and delegated collaboration lanes.', 'group' => 'intelligence'],
            ['label' => 'Executions', 'route' => 'intelligence.executions', 'slug' => 'intelligence-executions', 'description' => 'Queued and running multi-agent sessions with resumable execution records.', 'group' => 'intelligence'],
            ['label' => 'Reasoning', 'route' => 'intelligence.reasoning', 'slug' => 'intelligence-reasoning', 'description' => 'Goal, plan, evidence, and confidence metadata for multi-agent reasoning chains.', 'group' => 'intelligence'],
            ['label' => 'Approvals', 'route' => 'intelligence.approvals', 'slug' => 'intelligence-approvals', 'description' => 'Manager, executive, and administrator approval gates for controlled execution.', 'group' => 'intelligence'],
            ['label' => 'Communication', 'route' => 'intelligence.communication', 'slug' => 'intelligence-communication', 'description' => 'Inter-agent requests, responses, handovers, and evidence-linked messages.', 'group' => 'intelligence'],
            ['label' => 'Shared Memory', 'route' => 'intelligence.shared-memory', 'slug' => 'intelligence-shared-memory', 'description' => 'Shared documents, context packs, tool results, and temporary team memory.', 'group' => 'intelligence'],
            ['label' => 'Performance', 'route' => 'intelligence.performance', 'slug' => 'intelligence-performance', 'description' => 'Latency, token, cost, and agent utilization metrics across executions.', 'group' => 'intelligence'],
            ['label' => 'Quality', 'route' => 'intelligence.quality', 'slug' => 'intelligence-quality', 'description' => 'Completeness, evidence, policy, and verification scoring for reviewer outcomes.', 'group' => 'intelligence'],
            ['label' => 'Decision Records', 'route' => 'intelligence.decision-records', 'slug' => 'intelligence-decision-records', 'description' => 'Chosen paths, alternatives, risks, and supporting tool and knowledge evidence.', 'group' => 'intelligence'],
            ['label' => 'Monitoring', 'route' => 'intelligence.monitoring', 'slug' => 'intelligence-monitoring', 'description' => 'Running agents, queues, approvals, health, and runtime oversight metrics.', 'group' => 'intelligence'],
            ['label' => 'Executive Dashboard', 'route' => 'intelligence.operations.executive-dashboard', 'slug' => 'intelligence-executive-dashboard', 'description' => 'Top-level enterprise mission orchestration, health, and autonomous operations posture.', 'group' => 'intelligence'],
            ['label' => 'Enterprise Missions', 'route' => 'intelligence.operations.enterprise-missions', 'slug' => 'intelligence-enterprise-missions', 'description' => 'Mission ownership, budgets, deadlines, health, dependencies, and progress lifecycle.', 'group' => 'intelligence'],
            ['label' => 'Mission Timeline', 'route' => 'intelligence.operations.mission-timeline', 'slug' => 'intelligence-mission-timeline', 'description' => 'Historical mission checkpoints, milestones, events, and execution recovery records.', 'group' => 'intelligence'],
            ['label' => 'Operations Centre', 'route' => 'intelligence.operations.operations-centre', 'slug' => 'intelligence-operations-centre', 'description' => 'Cross-mission orchestration, supervision, queue posture, and incident recovery visibility.', 'group' => 'intelligence'],
            ['label' => 'Execution Centre', 'route' => 'intelligence.operations.execution-centre', 'slug' => 'intelligence-execution-centre', 'description' => 'Long-running execution control, checkpoints, retries, and resumable workflow state.', 'group' => 'intelligence'],
            ['label' => 'Mission Planner', 'route' => 'intelligence.operations.mission-planner', 'slug' => 'intelligence-mission-planner', 'description' => 'Versioned planning, critical path analysis, requirements, alternatives, and cost estimates.', 'group' => 'intelligence'],
            ['label' => 'Agent Marketplace', 'route' => 'intelligence.operations.agent-marketplace', 'slug' => 'intelligence-agent-marketplace', 'description' => 'Agent discovery, capabilities, provider support, skills, availability, and ranking.', 'group' => 'intelligence'],
            ['label' => 'Simulation Studio', 'route' => 'intelligence.operations.simulation-studio', 'slug' => 'intelligence-simulation-studio', 'description' => 'What-if simulation runs for provider outages, failures, budget shocks, and compliance events.', 'group' => 'intelligence'],
            ['label' => 'Predictions', 'route' => 'intelligence.operations.predictions', 'slug' => 'intelligence-predictions', 'description' => 'Predictive intelligence for delays, overruns, quality degradation, and overload risks.', 'group' => 'intelligence'],
            ['label' => 'Operations Compliance', 'route' => 'intelligence.operations.compliance', 'slug' => 'intelligence-operations-compliance', 'description' => 'Mission compliance evaluations across POPIA, GDPR, ISO, and governance policy flows.', 'group' => 'intelligence'],
            ['label' => 'Operations Policies', 'route' => 'intelligence.operations.policies', 'slug' => 'intelligence-operations-policies', 'description' => 'Token, tool, provider, security, and budget policies evaluated for every mission.', 'group' => 'intelligence'],
            ['label' => 'Operations Approvals', 'route' => 'intelligence.operations.approvals', 'slug' => 'intelligence-operations-approvals', 'description' => 'Parallel, sequential, delegated, and escalated approvals with signature traceability.', 'group' => 'intelligence'],
            ['label' => 'Enterprise Monitoring', 'route' => 'intelligence.operations.enterprise-monitoring', 'slug' => 'intelligence-enterprise-monitoring', 'description' => 'Execution duration, queue depth, retries, provider availability, and bottleneck monitoring.', 'group' => 'intelligence'],
            ['label' => 'Decision Records', 'route' => 'intelligence.operations.decision-records', 'slug' => 'intelligence-operations-decision-records', 'description' => 'Strategic decision analysis with alternatives, evidence, approvals, outcomes, and lessons.', 'group' => 'intelligence'],
            ['label' => 'Enterprise KPIs', 'route' => 'intelligence.operations.enterprise-kpis', 'slug' => 'intelligence-enterprise-kpis', 'description' => 'Mission success, autonomy, recovery, quality, utilisation, and productivity KPIs.', 'group' => 'intelligence'],
            ['label' => 'Autonomy Analytics', 'route' => 'intelligence.operations.autonomy-analytics', 'slug' => 'intelligence-autonomy-analytics', 'description' => 'Autonomous vs human intervention analytics and enterprise execution efficiency signals.', 'group' => 'intelligence'],
            ['label' => 'Commercial Packages', 'route' => 'intelligence.commercial.packages', 'slug' => 'intelligence-commercial-packages', 'description' => 'Enterprise package catalog, entitlements, pricing rules, and upgrade paths.', 'group' => 'intelligence'],
            ['label' => 'Commercial Tenants', 'route' => 'intelligence.commercial.tenants', 'slug' => 'intelligence-commercial-tenants', 'description' => 'Client organization portfolio, deployment modes, and tenant readiness posture.', 'group' => 'intelligence'],
            ['label' => 'Provisioning Queue', 'route' => 'intelligence.commercial.provisioning', 'slug' => 'intelligence-commercial-provisioning', 'description' => 'Tenant onboarding workflow from draft through provisioning and active service.', 'group' => 'intelligence'],
            ['label' => 'Subscriptions', 'route' => 'intelligence.commercial.subscriptions', 'slug' => 'intelligence-commercial-subscriptions', 'description' => 'Commercial subscriptions, account ownership, package alignment, and renewals.', 'group' => 'intelligence'],
            ['label' => 'Commercial Usage', 'route' => 'intelligence.commercial.usage', 'slug' => 'intelligence-commercial-usage', 'description' => 'Metered executions, quotas, overages, and non-destructive commercial enforcement.', 'group' => 'intelligence'],
            ['label' => 'Billing Readiness', 'route' => 'intelligence.commercial.billing', 'slug' => 'intelligence-commercial-billing', 'description' => 'Billing accounts, invoice payload readiness, contacts, and payment preparation.', 'group' => 'intelligence'],
            ['label' => 'Proposal Pipeline', 'route' => 'intelligence.commercial.proposals', 'slug' => 'intelligence-commercial-proposals', 'description' => 'Commercial proposals, approval stages, assumptions, and handover structure.', 'group' => 'intelligence'],
            ['label' => 'Deployments', 'route' => 'intelligence.commercial.deployments', 'slug' => 'intelligence-commercial-deployments', 'description' => 'Deployment runbooks, steps, evidence, and support transition operations.', 'group' => 'intelligence'],
            ['label' => 'Support Operations', 'route' => 'intelligence.commercial.support', 'slug' => 'intelligence-commercial-support', 'description' => 'Support plans, tickets, escalations, and SLA posture for active clients.', 'group' => 'intelligence'],
            ['label' => 'Release Readiness', 'route' => 'intelligence.commercial.readiness', 'slug' => 'intelligence-commercial-readiness', 'description' => 'Deployment readiness checks, commercial launch blockers, and cutover confidence.', 'group' => 'intelligence'],
            ['label' => 'Commercial Settings', 'route' => 'intelligence.commercial.settings', 'slug' => 'intelligence-commercial-settings', 'description' => 'Commercial operating defaults for packaging, readiness, billing, and support.', 'group' => 'intelligence'],
            ...AdminConsoleNavigationService::items(),
            ['label' => 'Agent Settings', 'route' => 'intelligence.agent-settings', 'slug' => 'intelligence-agent-settings', 'description' => 'Default execution posture, approval roles, and multi-agent runtime controls.', 'group' => 'intelligence'],
            ['label' => 'Usage', 'route' => 'intelligence.usage', 'slug' => 'intelligence-usage', 'description' => 'Historical provider usage, token accounting, and latency scaffolding.', 'group' => 'intelligence'],
            ['label' => 'Settings', 'route' => 'intelligence.settings', 'slug' => 'intelligence-settings', 'description' => 'Runtime defaults, streaming posture, and token-limit configuration.', 'group' => 'intelligence'],
            ['label' => 'Planner', 'route' => 'intelligence.planner', 'slug' => 'intelligence-planner', 'description' => 'Execution planning, dependencies, and complexity estimation.', 'group' => 'intelligence'],
            ['label' => 'Memory', 'route' => 'intelligence.memory', 'slug' => 'intelligence-memory', 'description' => 'Semantic memory review, relevance, and visibility controls.', 'group' => 'intelligence'],
            ['label' => 'Prompt Registry', 'route' => 'intelligence.prompts', 'slug' => 'intelligence-prompts', 'description' => 'Versioned runtime prompts and rendering controls.', 'group' => 'intelligence'],
            ['label' => 'Routing', 'route' => 'intelligence.routing', 'slug' => 'intelligence-routing', 'description' => 'Capability-based model routing and fallback rules.', 'group' => 'intelligence'],
            ['label' => 'Traces', 'route' => 'intelligence.traces', 'slug' => 'intelligence-traces', 'description' => 'Execution traces, replay data, and completion reasons.', 'group' => 'intelligence'],
            ['label' => 'Workflows', 'route' => 'intelligence.workflows', 'slug' => 'intelligence-workflows', 'description' => 'Workflow runs, checkpoints, and rollback payloads.', 'group' => 'intelligence'],
            ['label' => 'Diagnostics', 'route' => 'intelligence.diagnostics', 'slug' => 'intelligence-diagnostics', 'description' => 'Provider diagnostics, streaming posture, and runtime health.', 'group' => 'intelligence'],
            ['label' => 'Organizations', 'route' => 'platform.organizations', 'slug' => 'organizations', 'description' => 'Enterprise tenants and ERP client ownership boundaries.', 'group' => 'platform'],
            ['label' => 'Connections', 'route' => 'platform.connections', 'slug' => 'connections', 'description' => 'Secure ERP connectivity, API keys, and rate governance seams.', 'group' => 'platform'],
            ['label' => 'Gateway', 'route' => 'platform.gateway', 'slug' => 'gateway', 'description' => 'Provider-agnostic AI gateway capability contracts and request flow scaffolding.', 'group' => 'platform'],
            ['label' => 'Providers', 'route' => 'platform.providers', 'slug' => 'providers', 'description' => 'Provider registry, capabilities, and switchable driver contracts.', 'group' => 'platform'],
            ['label' => 'Models', 'route' => 'platform.models', 'slug' => 'models', 'description' => 'Catalog preparation for model availability, guardrails, and lifecycle control.', 'group' => 'platform'],
            ['label' => 'Knowledge', 'route' => 'platform.knowledge', 'slug' => 'knowledge', 'description' => 'Knowledge collections, chunking metadata, and permission boundaries.', 'group' => 'platform'],
            ['label' => 'Documents', 'route' => 'platform.documents', 'slug' => 'documents', 'description' => 'Document ingestion, OCR preparation, and storage extension points.', 'group' => 'platform'],
            ['label' => 'Prompt Library', 'route' => 'platform.prompt-library', 'slug' => 'prompt-library', 'description' => 'Versioned prompt governance for system, organization, and agent scopes.', 'group' => 'platform'],
            ['label' => 'Tools', 'route' => 'platform.tools', 'slug' => 'tools', 'description' => 'Registry preparation for ERP-discovered tools and callable contracts.', 'group' => 'platform'],
            ['label' => 'Agents', 'route' => 'platform.agents', 'slug' => 'agents', 'description' => 'Agent orchestration seams, policies, and capability definitions.', 'group' => 'platform'],
            ['label' => 'Workflows', 'route' => 'platform.workflows', 'slug' => 'workflows', 'description' => 'Workflow blueprints for queued, auditable enterprise actions.', 'group' => 'platform'],
            ['label' => 'Monitoring', 'route' => 'platform.monitoring', 'slug' => 'monitoring', 'description' => 'Usage, latency, failures, GPU telemetry, and queue health preparation.', 'group' => 'platform'],
            ['label' => 'Billing', 'route' => 'platform.billing', 'slug' => 'billing', 'description' => 'Subscriptions, rate cards, invoices, and usage accounting seams.', 'group' => 'platform'],
            ['label' => 'Analytics', 'route' => 'platform.analytics', 'slug' => 'analytics', 'description' => 'Cross-domain intelligence and reporting surface preparation.', 'group' => 'platform'],
            ['label' => 'Audit', 'route' => 'platform.audit', 'slug' => 'audit', 'description' => 'Immutable activity streams and compliance-ready traceability scaffolding.', 'group' => 'platform'],
            ['label' => 'Settings', 'route' => 'platform.settings', 'slug' => 'settings', 'description' => 'Centralized platform configuration and feature governance surfaces.', 'group' => 'platform'],
            ['label' => 'System', 'route' => 'platform.system', 'slug' => 'system', 'description' => 'Runtime posture, deployment preparation, and infrastructure extension points.', 'group' => 'platform'],
            ['label' => 'Health', 'route' => 'platform.health', 'slug' => 'health', 'description' => 'Health probes, incident readiness, and operational checkpoints.', 'group' => 'platform'],
        ];

        if (! config('deployment.commercial_console_enabled', false)) {
            $items = array_values(array_filter(
                $items,
                static fn (array $item): bool => ! str_starts_with($item['route'], 'intelligence.commercial.'),
            ));
        }

        return $items;
    }

    /**
     * @return list<array{label: string, route: string, slug: string, description: string, group: string}>
     */
    public static function pages(): array
    {
        return array_values(
            array_filter(
                self::items(),
                static fn (array $item): bool => $item['group'] === 'platform' && $item['slug'] !== 'dashboard',
            ),
        );
    }

    /**
     * @return array{label: string, route: string, slug: string, description: string, group: string}|null
     */
    public static function find(string $slug): ?array
    {
        foreach (self::items() as $item) {
            if ($item['slug'] === $slug) {
                return $item;
            }
        }

        return null;
    }
}
