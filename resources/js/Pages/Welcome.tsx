import ApplicationMark from '@/Components/Vip/ApplicationMark';
import { Head, Link } from '@inertiajs/react';

export default function Welcome({
    canLogin,
    canRegister,
    laravelVersion,
    phpVersion,
}: {
    canLogin: boolean;
    canRegister: boolean;
    laravelVersion: string;
    phpVersion: string;
}) {
    return (
        <>
            <Head title="VIP" />
            <div className="vip-landing">
                <div className="vip-landing__frame" />
                <div className="vip-landing__content">
                    <ApplicationMark />
                    <p className="vip-eyebrow">Foundational architecture</p>
                    <h1 className="vip-landing__title">
                        The intelligence layer for an entire ERP ecosystem.
                    </h1>
                    <p className="vip-landing__description">
                        VIP is a standalone Laravel 12 platform prepared to
                        deliver secure, provider-agnostic AI capabilities across
                        AB4IR ERP, POA ERP, Media ERP, GPERP, and future
                        enterprise systems.
                    </p>

                    <div className="vip-landing__actions">
                        {canLogin && (
                            <Link href={route('login')} className="vip-button">
                                Enter platform
                            </Link>
                        )}
                        {canRegister && (
                            <Link
                                href={route('register')}
                                className="vip-button vip-button--ghost"
                            >
                                Create access
                            </Link>
                        )}
                    </div>

                    <div className="vip-landing__meta">
                        <span>Laravel {laravelVersion}</span>
                        <span>PHP {phpVersion}</span>
                        <span>Phase 1 scaffold only</span>
                    </div>
                </div>
            </div>
        </>
    );
}
