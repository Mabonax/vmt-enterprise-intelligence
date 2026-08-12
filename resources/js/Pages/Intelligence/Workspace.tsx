import Surface from '@/Components/Vip/Surface';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { PageProps } from '@/types';
import { Head } from '@inertiajs/react';

type WorkspaceProps = PageProps<{
    page: {
        title: string;
        slug: string;
        description: string;
        eyebrow: string;
        cards: Array<{
            title: string;
            body: string;
        }>;
    };
    metrics: Array<{
        label: string;
        value: string;
        detail: string;
    }>;
    sections: Array<{
        title: string;
        items: Array<Record<string, string | number | boolean | null>>;
    }>;
}>;

export default function Workspace({ page, metrics, sections }: WorkspaceProps) {
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
                {metrics.map((metric) => (
                    <Surface
                        key={metric.label}
                        title={metric.label}
                        description={metric.detail}
                    >
                        <div className="vip-placeholder">
                            <span>Runtime signal</span>
                            <p className="text-4xl font-semibold tracking-tight text-zinc-100">
                                {metric.value}
                            </p>
                        </div>
                    </Surface>
                ))}

                {page.cards.map((card, index) => (
                    <Surface
                        key={`${page.slug}-${card.title}`}
                        title={card.title}
                        description={card.body}
                        tone={index === 0 ? 'accent' : 'muted'}
                    >
                        <div className="vip-placeholder">
                            <span>Runtime phase</span>
                            <p>
                                Planning, workflows, verification, replay,
                                memory, enterprise connectors, marketplace
                                governance, knowledge orchestration, and
                                provider-neutral execution now persist through
                                the intelligence domain.
                            </p>
                        </div>
                    </Surface>
                ))}

                {sections.map((section) => (
                    <Surface
                        key={`${page.slug}-${section.title}`}
                        title={section.title}
                        description={`${section.items.length} records`}
                    >
                        <div className="space-y-3 text-sm text-zinc-300">
                            {section.items.length === 0 ? (
                                <p>No records yet.</p>
                            ) : (
                                section.items.map((item, index) => (
                                    <div
                                        key={`${section.title}-${index}`}
                                        className="rounded-2xl border border-zinc-800 bg-zinc-950/70 p-3"
                                    >
                                        {Object.entries(item).map(([key, value]) => (
                                            <p key={key}>
                                                <span className="text-zinc-500">{key}:</span>{' '}
                                                {String(value ?? '')}
                                            </p>
                                        ))}
                                    </div>
                                ))
                            )}
                        </div>
                    </Surface>
                ))}
            </div>
        </AuthenticatedLayout>
    );
}
