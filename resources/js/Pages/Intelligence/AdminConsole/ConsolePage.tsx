import Surface from '@/Components/Vip/Surface';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';

type SummaryCard = {
    label: string;
    value: string;
};

type AlertItem = {
    id: number;
    uuid: string;
    alert_type: string;
    severity: string;
    title: string;
    message: string;
    status: string;
    source_context: string | null;
    source_type: string | null;
    source_id: string | null;
    first_seen_at: string | null;
    acknowledged_at: string | null;
    resolved_at: string | null;
    metadata: Record<string, unknown>;
};

type ActionItem = {
    id: number;
    uuid: string;
    action_type: string;
    title: string;
    description: string | null;
    target_context: string | null;
    target_type: string | null;
    target_id: string | null;
    status: string;
    requested_at: string | null;
    approved_at: string | null;
    executed_at: string | null;
    result: Record<string, unknown>;
};

type AuditItem = {
    id: number;
    event_type: string;
    event_name: string;
    source_context: string | null;
    target_type: string | null;
    target_id: string | null;
    occurred_at: string | null;
    metadata: Record<string, unknown>;
};

type ConsoleProps = {
    page: {
        title: string;
        description: string;
        eyebrow: string;
    };
    navigation: Array<{
        label: string;
        route: string;
        slug: string;
        description: string;
        group: string;
    }>;
    dashboard: {
        name: string;
        slug: string;
        description: string | null;
        audience_type: string;
        layout_config: Record<string, unknown>;
        widget_config: Record<string, unknown>;
    };
    metrics: Record<string, string | number | Record<string, number>>;
    widgets: Array<{
        name: string;
        slug: string;
        widget_type: string;
        data_source: string;
        display_config: Record<string, unknown>;
    }>;
    alerts: AlertItem[];
    actions: ActionItem[];
    health: {
        overall_status: string;
        score: number;
        signals: Record<string, boolean>;
        recommendations: string[];
        captured_at: string | null;
    };
    readiness: {
        overall_status: string;
        checks: Record<string, boolean>;
        blockers: string[];
        recommendations: string[];
    };
    auditTimeline: AuditItem[];
    summaryCards: SummaryCard[];
    forms: {
        requestAction: string;
    };
    routes: {
        alerts: {
            acknowledge: string;
            resolve: string;
            dismiss: string;
        };
        actions: {
            approve: string;
            execute: string;
        };
    };
};

type ConsolePageProps = ConsoleProps & {
    focus: 'overview' | 'alerts' | 'actions' | 'audit' | 'health' | 'readiness';
};

function postToTemplate(template: string, id: number) {
    return template.replace(/__(ALERT|ACTION)__/g, String(id));
}

function renderMetricValue(value: string | number | Record<string, number>) {
    if (typeof value === 'object' && value !== null) {
        return Object.entries(value)
            .map(([key, entryValue]) => `${key}: ${entryValue}`)
            .join(' | ');
    }

    return String(value);
}

export default function ConsolePage(props: ConsolePageProps) {
    const { page, summaryCards, metrics, widgets, alerts, actions, health, readiness, auditTimeline, routes, forms, focus } = props;

    return (
        <AuthenticatedLayout
            header={
                <div>
                    <p className="vip-eyebrow">{page.eyebrow}</p>
                    <h1 className="vip-title">{page.title}</h1>
                    <p className="vip-description">{page.description}</p>
                </div>
            }
        >
            <Head title={page.title} />

            <div className="vip-grid">
                {summaryCards.map((card) => (
                    <Surface key={card.label} title={card.label} description="Operational snapshot">
                        <div className="vip-placeholder">
                            <span>Current signal</span>
                            <p className="text-4xl font-semibold tracking-tight text-zinc-100">{card.value}</p>
                        </div>
                    </Surface>
                ))}

                <Surface title="Command Metrics" description="Cross-domain platform counts and health indicators">
                    <div className="space-y-3 text-sm text-zinc-300">
                        {Object.entries(metrics).map(([key, value]) => (
                            <div key={key} className="rounded-2xl border border-zinc-800 bg-zinc-950/70 p-3">
                                <p className="text-zinc-500">{key.replaceAll('_', ' ')}</p>
                                <p className="mt-1 text-zinc-100">{renderMetricValue(value)}</p>
                            </div>
                        ))}
                    </div>
                </Surface>

                <Surface title="Widget Registry" description="Default admin-console widgets for this view">
                    <div className="space-y-3 text-sm text-zinc-300">
                        {widgets.length === 0 ? (
                            <p>No widgets configured.</p>
                        ) : (
                            widgets.map((widget) => (
                                <div key={widget.slug} className="rounded-2xl border border-zinc-800 bg-zinc-950/70 p-3">
                                    <p className="font-medium text-zinc-100">{widget.name}</p>
                                    <p className="mt-1 text-zinc-500">{widget.widget_type} via {widget.data_source}</p>
                                </div>
                            ))
                        )}
                    </div>
                </Surface>

                {(focus === 'overview' || focus === 'alerts') && (
                    <Surface title="Alerts" description="Open, acknowledged, resolved, and dismissed alerts">
                        <div className="space-y-3 text-sm text-zinc-300">
                            {alerts.length === 0 ? (
                                <p>No alerts available.</p>
                            ) : (
                                alerts.map((alert) => (
                                    <div key={alert.id} className="rounded-2xl border border-zinc-800 bg-zinc-950/70 p-4">
                                        <p className="font-medium text-zinc-100">{alert.title}</p>
                                        <p className="mt-1 text-zinc-500">{alert.severity} | {alert.status} | {alert.source_context ?? 'general'}</p>
                                        <p className="mt-2">{alert.message}</p>
                                        <div className="mt-4 flex flex-wrap gap-2">
                                            <button className="vip-button vip-button--ghost" onClick={() => router.post(postToTemplate(routes.alerts.acknowledge, alert.id))}>Acknowledge</button>
                                            <button className="vip-button vip-button--ghost" onClick={() => router.post(postToTemplate(routes.alerts.resolve, alert.id))}>Resolve</button>
                                            <button className="vip-button vip-button--ghost" onClick={() => router.post(postToTemplate(routes.alerts.dismiss, alert.id))}>Dismiss</button>
                                        </div>
                                    </div>
                                ))
                            )}
                        </div>
                    </Surface>
                )}

                {(focus === 'overview' || focus === 'actions') && (
                    <Surface title="Action Queue" description="Safe internal actions with approval and execution controls">
                        <div className="mb-4 rounded-2xl border border-zinc-800 bg-zinc-950/70 p-4">
                            <button
                                className="vip-button"
                                onClick={() =>
                                    router.post(forms.requestAction, {
                                        action_type: 'capture_health_snapshot',
                                        title: 'Capture health snapshot',
                                        description: 'Refresh the current health snapshot from the admin console.',
                                        target_context: 'health',
                                        payload: { source: 'admin-console-ui' },
                                    })
                                }
                            >
                                Request Health Snapshot
                            </button>
                        </div>
                        <div className="space-y-3 text-sm text-zinc-300">
                            {actions.length === 0 ? (
                                <p>No actions in queue.</p>
                            ) : (
                                actions.map((action) => (
                                    <div key={action.id} className="rounded-2xl border border-zinc-800 bg-zinc-950/70 p-4">
                                        <p className="font-medium text-zinc-100">{action.title}</p>
                                        <p className="mt-1 text-zinc-500">{action.action_type} | {action.status} | {action.target_context ?? 'general'}</p>
                                        {action.description ? <p className="mt-2">{action.description}</p> : null}
                                        <div className="mt-4 flex flex-wrap gap-2">
                                            <button className="vip-button vip-button--ghost" onClick={() => router.post(postToTemplate(routes.actions.approve, action.id))}>Approve</button>
                                            <button className="vip-button vip-button--ghost" onClick={() => router.post(postToTemplate(routes.actions.execute, action.id))}>Execute</button>
                                        </div>
                                    </div>
                                ))
                            )}
                        </div>
                    </Surface>
                )}

                {(focus === 'overview' || focus === 'health') && (
                    <Surface title="Platform Health" description="Latest score, degraded signals, and recommendations">
                        <div className="space-y-4 text-sm text-zinc-300">
                            <div className="vip-placeholder">
                                <span>Health status</span>
                                <p className="text-4xl font-semibold tracking-tight text-zinc-100">
                                    {health.score}% | {health.overall_status}
                                </p>
                            </div>
                            <div className="grid gap-3 md:grid-cols-2">
                                {Object.entries(health.signals).map(([key, ok]) => (
                                    <div key={key} className="rounded-2xl border border-zinc-800 bg-zinc-950/70 p-3">
                                        <p className="text-zinc-500">{key.replaceAll('_', ' ')}</p>
                                        <p className={ok ? 'text-zinc-100' : 'text-amber-300'}>{ok ? 'healthy' : 'attention required'}</p>
                                    </div>
                                ))}
                            </div>
                            <div className="space-y-2">
                                {health.recommendations.map((recommendation) => (
                                    <p key={recommendation} className="rounded-2xl border border-zinc-800 bg-zinc-950/70 p-3">{recommendation}</p>
                                ))}
                            </div>
                        </div>
                    </Surface>
                )}

                {(focus === 'overview' || focus === 'readiness') && (
                    <Surface title="Readiness" description="Checklist, blockers, and next actions">
                        <div className="space-y-4 text-sm text-zinc-300">
                            <div className="vip-placeholder">
                                <span>Overall readiness</span>
                                <p className="text-4xl font-semibold tracking-tight text-zinc-100">{readiness.overall_status}</p>
                            </div>
                            <div className="grid gap-3 md:grid-cols-2">
                                {Object.entries(readiness.checks).map(([key, ok]) => (
                                    <div key={key} className="rounded-2xl border border-zinc-800 bg-zinc-950/70 p-3">
                                        <p className="text-zinc-500">{key.replaceAll('_', ' ')}</p>
                                        <p className={ok ? 'text-zinc-100' : 'text-amber-300'}>{ok ? 'ready' : 'blocked'}</p>
                                    </div>
                                ))}
                            </div>
                            <div className="space-y-2">
                                {(readiness.blockers.length === 0 ? ['No blockers recorded.'] : readiness.blockers).map((blocker) => (
                                    <p key={blocker} className="rounded-2xl border border-zinc-800 bg-zinc-950/70 p-3">{blocker}</p>
                                ))}
                            </div>
                        </div>
                    </Surface>
                )}

                {(focus === 'overview' || focus === 'audit') && (
                    <Surface title="Audit Timeline" description="Searchable history of admin-console changes">
                        <div className="space-y-3 text-sm text-zinc-300">
                            {auditTimeline.length === 0 ? (
                                <p>No audit events yet.</p>
                            ) : (
                                auditTimeline.map((event) => (
                                    <div key={event.id} className="rounded-2xl border border-zinc-800 bg-zinc-950/70 p-3">
                                        <p className="font-medium text-zinc-100">{event.event_name}</p>
                                        <p className="mt-1 text-zinc-500">{event.event_type} | {event.source_context ?? 'general'} | {event.occurred_at ?? 'unknown time'}</p>
                                    </div>
                                ))
                            )}
                        </div>
                    </Surface>
                )}
            </div>
        </AuthenticatedLayout>
    );
}
