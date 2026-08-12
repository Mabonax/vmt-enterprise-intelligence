import Surface from '@/Components/Vip/Surface';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { PageProps } from '@/types';
import { Head } from '@inertiajs/react';

type DashboardProps = PageProps<{
    hero: {
        eyebrow: string;
        title: string;
        description: string;
    };
    widgets: Array<{
        title: string;
        description: string;
    }>;
}>;

export default function Dashboard({ hero, widgets }: DashboardProps) {
    return (
        <AuthenticatedLayout
            header={
                <div>
                    <p className="vip-eyebrow">{hero.eyebrow}</p>
                    <h1 className="vip-title">{hero.title}</h1>
                    <p className="vip-description">{hero.description}</p>
                </div>
            }
        >
            <Head title="Dashboard" />

            <div className="vip-grid">
                {widgets.map((widget) => (
                    <Surface
                        key={widget.title}
                        title={widget.title}
                        description={widget.description}
                        tone="muted"
                    >
                        <div className="vip-placeholder">
                            <span>Phase 1 scaffold</span>
                            <p>No live metrics are fabricated in this foundation.</p>
                        </div>
                    </Surface>
                ))}

                <Surface
                    title="Architecture posture"
                    description="Replaceable, modular, and queue-safe by design."
                    tone="accent"
                >
                    <ul className="vip-checklist">
                        <li>Conversation runtime APIs are centralized behind one manager service.</li>
                        <li>Provider contracts are isolated from application logic and discovered at boot.</li>
                        <li>Docker, Redis, queue, and admin shell seams remain ready for later intelligence phases.</li>
                    </ul>
                </Surface>

                <Surface
                    title="Immediate next phase"
                    description="Phase 3 can add real provider integrations, retrieval, and enterprise workflows without replacing this runtime."
                >
                    <div className="vip-placeholder">
                        <span>Recommended sequence</span>
                        <p>
                            Attach provider SDKs, introduce retrieval and memory
                            strategies, then wire domain-specific agents and
                            tools.
                        </p>
                    </div>
                </Surface>
            </div>
        </AuthenticatedLayout>
    );
}
