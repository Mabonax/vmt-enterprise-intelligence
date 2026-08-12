import ApplicationLogo from '@/Components/ApplicationLogo';
import { Link } from '@inertiajs/react';
import { PropsWithChildren } from 'react';

export default function Guest({ children }: PropsWithChildren) {
    return (
        <div className="vip-auth-shell">
            <div className="vip-auth-background" />
            <div className="vip-auth-copy">
                <p className="vip-auth-eyebrow">VIP foundation</p>
                <h1>Independent intelligence for every ERP in the VMT ecosystem.</h1>
                <p>
                    Laravel 12, Inertia, React, queue-first operations, and
                    provider-agnostic AI contracts.
                </p>
            </div>

            <div className="vip-auth-card">
                <Link href="/">
                    <ApplicationLogo className="mb-8" />
                </Link>

                {children}
            </div>
        </div>
    );
}
