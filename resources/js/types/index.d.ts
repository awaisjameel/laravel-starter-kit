import type { LucideIcon } from '@lucide/vue'
import type { Appearance, UserViewData } from './app-data'

export interface Auth {
    user: UserViewData | null
}

export interface BreadcrumbItem {
    title: string
    href: string
}

export type NavItemActiveMatch = 'exact' | 'prefix'

export interface NavItem {
    title: string
    href: string
    icon?: LucideIcon
    isActive?: boolean
    activeMatch?: NavItemActiveMatch
    external?: boolean
}

export type AppPageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    name: string
    quote: { message: string; author: string }
    auth: Auth
    flash: {
        message?: string
        error?: string
        status?: string
    }
    /**
     * Absolute URL of the current request, including the query string. Inertia's
     * `page.url` is relative, so this is the origin to resolve it against.
     */
    location: string
    sidebarOpen: boolean
    /**
     * Colour scheme the server rendered this response with, taken from the
     * `appearance` cookie. Seeds the appearance UI so it hydrates without a mismatch.
     */
    appearance: Appearance
}

export interface SelectOption {
    value: string
    label: string
}
