import Surface from '@/Components/Vip/Surface';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { PageProps } from '@/types';
import { Head, Link } from '@inertiajs/react';

type DashboardProps = PageProps<{
    hero: { eyebrow: string; title: string; description: string };
    widgets: Array<{ title: string; description: string }>;
}>;

const operatingAreas = [
    { title: 'Connected applications', description: 'Register enterprise clients, scope access and manage credentials.', href: '/intelligence/admin-console/connected-applications' },
    { title: 'Gateway operations', description: 'Inspect provider readiness, request execution and service health.', href: '/intelligence' },
];

export default function Dashboard({ hero, widgets }: DashboardProps) {
    return (
        <AuthenticatedLayout
            header={
                <div>
                    <p className="vip-eyebrow">VMT Enterprise AI Gateway</p>
                    <h1 className="vip-title">{hero.title}</h1>
                    <p className="vip-description">{hero.description}</p>
                </div>
            }
        >
            <Head title="Command Center" />
            <div className="vip-grid">
                {widgets.map((widget) => (
                    <Surface key={widget.title} title={widget.title} description={widget.description} tone="muted">
                        <p className="text-sm text-zinc-400">Explore this operational area to view its current state.</p>
                    </Surface>
                ))}
                {operatingAreas.map((area) => (
                    <Surface key={area.title} title={area.title} description={area.description} tone="accent">
                        <Link className="vip-button vip-button--ghost" href={area.href}>Open workspace</Link>
                    </Surface>
                ))}
            </div>
        </AuthenticatedLayout>
    );
}
