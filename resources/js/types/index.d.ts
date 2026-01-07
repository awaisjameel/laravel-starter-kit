import type { LucideIcon } from 'lucide-vue-next';
import type { Config } from 'ziggy-js';
import { UserData } from './app-data';

export interface Auth {
    user: UserData;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon;
    isActive?: boolean;
}

export type AppPageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    ziggy: Config & { location: string };
    sidebarOpen: boolean;
};

export type User = UserData;

export interface Paginated<T> {
    data: T[];
    per_page: number;
    current_page: number;
    from: number;
    to: number;
    last_page: number;
    total: number;
}

export interface UsersPageProps {
    users: Paginated<UserData>;
}

export type BreadcrumbItemType = BreadcrumbItem;
