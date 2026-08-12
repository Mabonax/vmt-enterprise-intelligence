import { PropsWithChildren } from 'react';

type SurfaceProps = PropsWithChildren<{
    title: string;
    description: string;
    tone?: 'default' | 'muted' | 'accent';
}>;

export default function Surface({
    title,
    description,
    tone = 'default',
    children,
}: SurfaceProps) {
    return (
        <section className={`vip-surface vip-surface--${tone}`}>
            <header className="vip-surface__header">
                <div>
                    <h3>{title}</h3>
                    <p>{description}</p>
                </div>
            </header>
            <div className="vip-surface__body">{children}</div>
        </section>
    );
}
