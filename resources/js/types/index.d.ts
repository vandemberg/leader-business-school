import { Config } from 'ziggy-js';

export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at: string;
}

export interface Platform {
    id: number;
    name: string;
    slug: string;
    brand?: string;
}

export interface PlatformData {
    current: Platform | null;
    available: Platform[];
    show_selector: boolean;
}

export type PageProps<T extends Record<string, unknown> = Record<string, unknown>> =
    T & {
        auth: {
            user: User;
        };
        platform?: PlatformData;
        ziggy: Config & { location: string };
    };
