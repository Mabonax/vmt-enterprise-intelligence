import Surface from '@/Components/Vip/Surface';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { PageProps } from '@/types';
import { Head } from '@inertiajs/react';

type PlatformPageProps = PageProps<{
    page: {
        title: string;
        slug: string;
        description: string;
        pillars: Array<{
            title: string;
            body: string;
        }>;
    };
}>;

export default function Show({ page }: PlatformPageProps) {
    return (
        <AuthenticatedLayout
            header={
                <div>
                    <p className="vip-eyebrow">Enterprise gateway domain</p>
                    <h1 className="vip-title">{page.title}</h1>
                    <p className="vip-description">{page.description}</p>
                </div>
            }
        >
            <Head title={page.title} />

            <div className="vip-grid">
                {page.pillars.map((pillar) => (
                    <Surface
                        key={pillar.title}
                        title={pillar.title}
                        description={pillar.body}
                    >
                        <div className="vip-placeholder">
                            <span>Gateway-aligned workspace</span>
                            <p>
                                This domain is retained as part of the
                                Enterprise AI Gateway and evolves through
                                additive contracts, services, and operational
                                controls.
                            </p>
                        </div>
                    </Surface>
                ))}
            </div>
        </AuthenticatedLayout>
    );
}
