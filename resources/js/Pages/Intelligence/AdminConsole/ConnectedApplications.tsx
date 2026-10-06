import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router, useForm } from '@inertiajs/react';

type Props = {
    page: { title: string; eyebrow: string; description: string };
    tenants: Array<{ id: string; name: string; status: string; clients_count: number }>;
    clients: Array<{
        id: string;
        tenant: string | null;
        name: string;
        environment: string;
        status: string;
        providers: string[];
        models: string[];
        capabilities: string[];
        credentials: Array<{ key_identifier: string; status: string; version: number; auth_method: string }>;
    }>;
    organizations: Array<{ id: string; name: string; code: string }>;
    defaults: { provider: string; model: string; capabilities: string[] };
    routes: { storeTenant: string; storeClient: string; issueKey: string; revokeKey: string };
    flash?: { status?: string | null; gatewayCredential?: Record<string, string | number | null> | null };
};

export default function ConnectedApplications(props: Props) {
    const tenant = useForm({
        organization_id: props.organizations[0]?.id ?? '',
        name: '',
        slug: '',
    });

    const client = useForm({
        gateway_tenant_id: props.tenants[0]?.id ?? '',
        name: '',
        description: '',
        client_type: 'erp',
        environment: 'production',
        enabled_providers: [props.defaults.provider],
        enabled_models: [props.defaults.model],
        enabled_capabilities: props.defaults.capabilities.filter((value) =>
            ['chat', 'summarise', 'report', 'translate', 'classify', 'search', 'action'].includes(value),
        ),
        scopes: ['chat'],
        rate_limit_per_minute: 60,
        daily_quota: 100000,
    });

    const toggle = (capability: string) => {
        const active = client.data.enabled_capabilities.includes(capability);
        client.setData(
            'enabled_capabilities',
            active
                ? client.data.enabled_capabilities.filter((item) => item !== capability)
                : [...client.data.enabled_capabilities, capability],
        );
    };

    const clientRoute = (template: string, id: string) => template.replace('__CLIENT__', id);
    const keyRoute = (template: string, id: string, key: string) =>
        template.replace('__CLIENT__', id).replace('__KEY__', encodeURIComponent(key));

    return (
        <AuthenticatedLayout
            header={
                <div>
                    <p className="vip-eyebrow">{props.page.eyebrow}</p>
                    <h1 className="vip-title">{props.page.title}</h1>
                    <p className="vip-description">{props.page.description}</p>
                </div>
            }
        >
            <Head title={props.page.title} />

            <div className="space-y-6">
                {props.flash?.status && (
                    <div className="rounded-2xl border border-emerald-500/30 bg-emerald-500/10 p-4 text-sm text-emerald-200">
                        {props.flash.status}
                    </div>
                )}

                {props.flash?.gatewayCredential && (
                    <section className="rounded-3xl border border-amber-400/40 bg-amber-400/10 p-6">
                        <h2 className="text-lg font-semibold text-amber-100">One-time gateway credential</h2>
                        <p className="mt-1 text-sm text-amber-200/80">
                            Copy this credential material now. Secret values are not shown again.
                        </p>
                        <pre className="mt-4 overflow-x-auto rounded-2xl bg-zinc-950 p-4 text-xs text-zinc-100">
                            {JSON.stringify(props.flash.gatewayCredential, null, 2)}
                        </pre>
                    </section>
                )}

                <div className="grid gap-6 xl:grid-cols-2">
                    <form
                        onSubmit={(event) => {
                            event.preventDefault();
                            tenant.post(props.routes.storeTenant, { preserveScroll: true });
                        }}
                        className="space-y-4 rounded-3xl border border-zinc-800 bg-zinc-950/60 p-6"
                    >
                        <div>
                            <h2 className="text-xl font-semibold text-zinc-100">Create gateway tenant</h2>
                            <p className="text-sm text-zinc-400">Create an isolated gateway boundary for an organization.</p>
                        </div>
                        <select
                            value={tenant.data.organization_id}
                            onChange={(event) => tenant.setData('organization_id', event.target.value)}
                            className="w-full rounded-xl border-zinc-700 bg-zinc-900 text-zinc-100"
                        >
                            {props.organizations.map((organization) => (
                                <option key={organization.id} value={organization.id}>
                                    {organization.name}{organization.code ? ` · ${organization.code}` : ''}
                                </option>
                            ))}
                        </select>
                        <input
                            value={tenant.data.name}
                            onChange={(event) => tenant.setData('name', event.target.value)}
                            placeholder="Tenant name"
                            className="w-full rounded-xl border-zinc-700 bg-zinc-900 text-zinc-100"
                        />
                        <input
                            value={tenant.data.slug}
                            onChange={(event) => tenant.setData('slug', event.target.value)}
                            placeholder="Slug (optional)"
                            className="w-full rounded-xl border-zinc-700 bg-zinc-900 text-zinc-100"
                        />
                        <button className="rounded-xl bg-zinc-100 px-4 py-2 text-sm font-semibold text-zinc-950">
                            Create tenant
                        </button>
                    </form>

                    <form
                        onSubmit={(event) => {
                            event.preventDefault();
                            client.post(props.routes.storeClient, { preserveScroll: true });
                        }}
                        className="space-y-4 rounded-3xl border border-zinc-800 bg-zinc-950/60 p-6"
                    >
                        <div>
                            <h2 className="text-xl font-semibold text-zinc-100">Register application</h2>
                            <p className="text-sm text-zinc-400">Register an ERP and issue its first gateway key.</p>
                        </div>
                        <select
                            value={client.data.gateway_tenant_id}
                            onChange={(event) => client.setData('gateway_tenant_id', event.target.value)}
                            className="w-full rounded-xl border-zinc-700 bg-zinc-900 text-zinc-100"
                        >
                            {props.tenants.map((item) => (
                                <option key={item.id} value={item.id}>{item.name}</option>
                            ))}
                        </select>
                        <input
                            value={client.data.name}
                            onChange={(event) => client.setData('name', event.target.value)}
                            placeholder="Application name"
                            className="w-full rounded-xl border-zinc-700 bg-zinc-900 text-zinc-100"
                        />
                        <textarea
                            value={client.data.description}
                            onChange={(event) => client.setData('description', event.target.value)}
                            placeholder="Purpose of this ERP integration"
                            className="min-h-20 w-full rounded-xl border-zinc-700 bg-zinc-900 text-zinc-100"
                        />
                        <div className="flex flex-wrap gap-2">
                            {props.defaults.capabilities
                                .filter((value) => !['admin', 'metrics'].includes(value))
                                .map((capability) => (
                                    <button
                                        type="button"
                                        key={capability}
                                        onClick={() => toggle(capability)}
                                        className={
                                            client.data.enabled_capabilities.includes(capability)
                                                ? 'rounded-full border border-emerald-400/50 bg-emerald-400/10 px-3 py-1 text-xs text-emerald-200'
                                                : 'rounded-full border border-zinc-700 px-3 py-1 text-xs text-zinc-400'
                                        }
                                    >
                                        {capability}
                                    </button>
                                ))}
                        </div>
                        <div className="grid gap-3 md:grid-cols-2">
                            <input
                                value={client.data.enabled_providers[0] ?? ''}
                                onChange={(event) => client.setData('enabled_providers', [event.target.value])}
                                className="rounded-xl border-zinc-700 bg-zinc-900 text-zinc-100"
                            />
                            <input
                                value={client.data.enabled_models[0] ?? ''}
                                onChange={(event) => client.setData('enabled_models', [event.target.value])}
                                className="rounded-xl border-zinc-700 bg-zinc-900 text-zinc-100"
                            />
                        </div>
                        <button
                            disabled={props.tenants.length === 0}
                            className="rounded-xl bg-emerald-300 px-4 py-2 text-sm font-semibold text-zinc-950 disabled:opacity-40"
                        >
                            Register + issue key
                        </button>
                    </form>
                </div>

                <section className="space-y-4 rounded-3xl border border-zinc-800 bg-zinc-950/60 p-6">
                    <h2 className="text-xl font-semibold text-zinc-100">Connected applications</h2>
                    {props.clients.length === 0 && <p className="text-sm text-zinc-500">No applications registered.</p>}
                    {props.clients.map((item) => (
                        <article key={item.id} className="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-5">
                            <div className="flex flex-wrap items-start justify-between gap-4">
                                <div>
                                    <h3 className="font-semibold text-zinc-100">{item.name}</h3>
                                    <p className="mt-1 text-sm text-zinc-400">
                                        {item.tenant ?? 'No tenant'} · {item.environment} · {item.status}
                                    </p>
                                    <p className="mt-1 text-xs text-zinc-500">
                                        {item.providers.join(', ')} · {item.models.join(', ')}
                                    </p>
                                </div>
                                <button
                                    type="button"
                                    onClick={() => router.post(clientRoute(props.routes.issueKey, item.id), {}, { preserveScroll: true })}
                                    className="rounded-xl border border-zinc-700 px-3 py-2 text-xs text-zinc-200"
                                >
                                    Issue key
                                </button>
                            </div>
                            <div className="mt-4 flex flex-wrap gap-1.5">
                                {item.capabilities.map((capability) => (
                                    <span key={capability} className="rounded-full border border-zinc-700 px-2 py-1 text-[11px] text-zinc-400">
                                        {capability}
                                    </span>
                                ))}
                            </div>
                            <div className="mt-4 space-y-2 border-t border-zinc-800 pt-4">
                                {item.credentials.map((credential) => (
                                    <div key={credential.key_identifier} className="flex items-center justify-between gap-3 rounded-xl bg-zinc-950 p-3">
                                        <div>
                                            <code className="text-xs text-zinc-200">{credential.key_identifier}</code>
                                            <p className="text-xs text-zinc-500">
                                                v{credential.version} · {credential.auth_method} · {credential.status}
                                            </p>
                                        </div>
                                        {credential.status === 'active' && (
                                            <button
                                                type="button"
                                                onClick={() =>
                                                    router.post(
                                                        keyRoute(props.routes.revokeKey, item.id, credential.key_identifier),
                                                        {},
                                                        { preserveScroll: true },
                                                    )
                                                }
                                                className="text-xs font-medium text-rose-300"
                                            >
                                                Revoke
                                            </button>
                                        )}
                                    </div>
                                ))}
                            </div>
                        </article>
                    ))}
                </section>
            </div>
        </AuthenticatedLayout>
    );
}
