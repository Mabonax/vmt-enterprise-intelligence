import ApplicationMark from '@/Components/Vip/ApplicationMark';
import { Head, Link } from '@inertiajs/react';

export default function Welcome({
    canLogin,
    canRegister,
}: {
    canLogin: boolean;
    canRegister: boolean;
    laravelVersion: string;
    phpVersion: string;
}) {
    return (
        <>
            <Head title="VMT Enterprise AI Gateway" />
            <div className="vip-landing">
                <div className="vip-landing__frame" />
                <div className="vip-landing__content">
                    <ApplicationMark />
                    <p className="vip-eyebrow">VMT Enterprise AI Gateway</p>
                    <h1 className="vip-landing__title">
                        Your ERP. Your AI. Your infrastructure.
                    </h1>
                    <p className="vip-landing__description">
                        A dedicated, VMT-managed intelligence service deployed alongside your ERP.
                        Connect approved AI models, protect enterprise knowledge,
                        and audit every request—without public self-service accounts.
                    </p>
                    <div className="vip-landing__actions">
                        {canLogin && <Link href={route('login')} className="vip-button">Open command center</Link>}
                        {canRegister && (
                            <Link href={route('register')} className="vip-button vip-button--ghost">
                                Create operator account
                            </Link>
                        )}
                    </div>
                    <div className="vip-landing__meta">
                        <span>Provider-agnostic</span>
                        <span>Local-first</span>
                        <span>Auditable by design</span>
                    </div>
                </div>
            </div>
        </>
    );
}
