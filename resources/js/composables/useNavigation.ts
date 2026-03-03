import {
    buildDashboardFooterItems,
    buildDashboardPrimaryItems,
    buildMarketingFooterGroups,
    buildMarketingPrimaryAction,
    buildMarketingPrimaryItems,
    buildMarketingSecondaryAction,
    type NavigationGroup
} from '@/config/navigation'
import type { AppPageProps, NavItem } from '@/types'
import { UserRole } from '@/types/app-data'

export function useNavigation() {
    const page = usePage<AppPageProps>()

    const user = computed(() => page.props.auth?.user ?? null)
    const isAuthenticated = computed(() => Boolean(user.value?.id))
    const isAdmin = computed(() => user.value?.role === UserRole.Admin)

    const context = computed(() => ({
        isAuthenticated: isAuthenticated.value,
        isAdmin: isAdmin.value
    }))

    const dashboardPrimaryItems = computed<NavItem[]>(() => buildDashboardPrimaryItems(context.value))
    const dashboardFooterItems = computed<NavItem[]>(() => buildDashboardFooterItems())
    const marketingPrimaryItems = computed<NavItem[]>(() => buildMarketingPrimaryItems(context.value))
    const marketingPrimaryAction = computed<NavItem>(() => buildMarketingPrimaryAction(context.value))
    const marketingSecondaryAction = computed<NavItem>(() => buildMarketingSecondaryAction(context.value))
    const marketingFooterGroups = computed<NavigationGroup[]>(() => buildMarketingFooterGroups(context.value))

    return {
        isAuthenticated,
        isAdmin,
        dashboardPrimaryItems,
        dashboardFooterItems,
        marketingPrimaryItems,
        marketingPrimaryAction,
        marketingSecondaryAction,
        marketingFooterGroups
    }
}
