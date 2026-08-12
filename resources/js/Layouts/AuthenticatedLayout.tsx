import ApplicationLogo from '@/Components/ApplicationLogo';
import { PageProps } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { PropsWithChildren, ReactNode, useState } from 'react';

type NavigationItem = {
    label: string;
    route: string;
    slug: string;
    description: string;
};

export default function Authenticated({
    header,
    children,
}: PropsWithChildren<{ header?: ReactNode }>) {
    const { auth, navigation } = usePage<
        PageProps<{ navigation: NavigationItem[] }>
    >().props;
    const user = auth.user;
    const [showingNavigationDropdown, setShowingNavigationDropdown] =
        useState(false);

    if (!user) {
        return null;
    }

    return (
        <div className="vip-shell">
            <aside className="vip-sidebar">
                <div className="vip-sidebar__brand">
                    <Link href={route('dashboard')}>
                        <ApplicationLogo />
                    </Link>
                    <p className="vip-sidebar__caption">
                        Provider-neutral enterprise intelligence
                    </p>
                </div>

                <nav className="vip-sidebar__nav">
                    {navigation.map((item) => {
                        const active = route().current(item.route);

                        return (
                            <Link
                                key={item.slug}
                                href={route(item.route)}
                                className={`vip-sidebar__link ${active ? 'is-active' : ''}`}
                            >
                                <span>{item.label}</span>
                                <small>{item.description}</small>
                            </Link>
                        );
                    })}
                </nav>
            </aside>

            <div className="vip-main">
                <nav className="vip-topbar">
                    <div>
                        <p className="vip-topbar__eyebrow">VMT ecosystem</p>
                        <div className="vip-topbar__breadcrumbs">
                            <Link href={route('dashboard')}>Dashboard</Link>
                            <span>/</span>
                            <span>{route().current() ?? 'workspace'}</span>
                        </div>
                    </div>

                    <div className="vip-topbar__actions">
                        <button
                            type="button"
                            className="vip-topbar__menu"
                            onClick={() =>
                                setShowingNavigationDropdown((state) => !state)
                            }
                        >
                            Menu
                        </button>
                        <Link href={route('profile.edit')} className="vip-user">
                            <span>{user.name}</span>
                            <small>{user.email}</small>
                        </Link>
                    </div>
                </nav>

                {showingNavigationDropdown && (
                    <div className="vip-mobile-nav">
                        {navigation.map((item) => (
                            <Link
                                key={item.slug}
                                href={route(item.route)}
                                className="vip-mobile-nav__link"
                            >
                                {item.label}
                            </Link>
                        ))}
                        <Link
                            href={route('logout')}
                            method="post"
                            as="button"
                            className="vip-mobile-nav__link vip-mobile-nav__logout"
                        >
                            Log out
                        </Link>
                    </div>
                )}

                {header && <header className="vip-page-header">{header}</header>}

                <main className="vip-page-content">{children}</main>
            </div>
        </div>
    );
}
