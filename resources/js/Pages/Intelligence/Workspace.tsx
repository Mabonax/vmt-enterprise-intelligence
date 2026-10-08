import Surface from '@/Components/Vip/Surface';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { PageProps } from '@/types';
import { Head } from '@inertiajs/react';

type Value = string | number | boolean | null;
type WorkspaceProps = PageProps<{
    page: {
        title: string;
        slug: string;
        description: string;
        eyebrow: string;
        cards: Array<{ title: string; body: string }>;
    };
    metrics: Array<{ label: string; value: string; detail: string }>;
    sections: Array<{ title: string; items: Array<Record<string, Value>> }>;
}>;

function formatValue(value: Value): string {
    if (value === null || value === '') return '—';
    if (typeof value === 'boolean') return value ? 'Yes' : 'No';
    return String(value);
}

export default function Workspace({ page, metrics, sections }: WorkspaceProps) {
    return (
        <AuthenticatedLayout header={
            <div>
                <p className="vip-eyebrow">{page.eyebrow}</p>
                <h1 className="vip-title">{page.title}</h1>
                <p className="vip-description">{page.description}</p>
            </div>
        }>
            <Head title={page.title} />
            <div className="vip-grid">
                {metrics.map(metric => (
                    <Surface key={metric.label} title={metric.label} description={metric.detail}>
                        <p className="text-4xl font-semibold tracking-tight text-zinc-100">{metric.value}</p>
                    </Surface>
                ))}
                {sections.map(section => (
                    <Surface key={section.title} title={section.title} description={`${section.items.length} records`}>
                        {section.items.length === 0 ? (
                            <p className="rounded-2xl border border-dashed border-zinc-700 p-5 text-sm text-zinc-400">
                                No records available in this workspace.
                            </p>
                        ) : (
                            <div className="overflow-x-auto">
                                <table className="w-full text-left text-sm text-zinc-300">
                                    <thead className="border-b border-zinc-700 text-xs uppercase tracking-wide text-zinc-500">
                                        <tr>{Object.keys(section.items[0]).map(key => <th key={key} className="px-3 py-3">{key.replaceAll('_', ' ')}</th>)}</tr>
                                    </thead>
                                    <tbody>
                                        {section.items.map((item, index) => (
                                            <tr key={index} className="border-b border-zinc-800 last:border-0">
                                                {Object.keys(section.items[0]).map(key => <td key={key} className="px-3 py-3">{formatValue(item[key] ?? null)}</td>)}
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        )}
                    </Surface>
                ))}
                {page.cards.map(card => (
                    <Surface key={card.title} title={card.title} description={card.body} tone="muted" />
                ))}
            </div>
        </AuthenticatedLayout>
    );
}
