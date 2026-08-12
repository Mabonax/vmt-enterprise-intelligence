export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string;
    organization_id?: string | null;
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User | null;
        roles: string[];
    };
    navigation: Array<{
        label: string;
        route: string;
        slug: string;
        description: string;
    }>;
    platform: {
        name: string;
        tagline: string;
    };
};
