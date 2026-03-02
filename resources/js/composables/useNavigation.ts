import type { AppPageProps, NavItem } from '@/types'
import { UserRole } from '@/types/app-data'
import { LayoutGrid, LockKeyhole, LogIn, Settings, UserPlus, Users } from 'lucide-vue-next'

export interface NavigationGroup {
    title: string
    items: NavItem[]
}

export function useNavigation() {
    const page = usePage<AppPageProps>()

    const user = computed(() => page.props.auth?.user ?? null)
    const isAuthenticated = computed(() => Boolean(user.value?.id))
    const isAdmin = computed(() => user.value?.role === UserRole.Admin)

    const dashboardPrimaryItems = computed<NavItem[]>(() => {
        const items: NavItem[] = [
            {
                title: 'Dashboard',
                href: route('app.dashboard'),
                icon: LayoutGrid
            }
        ]

        if (isAdmin.value) {
            items.push({
                title: 'Users',
                href: route('app.admin.users.index'),
                icon: Users
            })
        }

        return items
    })

    const dashboardFooterItems = computed<NavItem[]>(() => [
        {
            title: 'Settings',
            href: route('app.settings.profile.edit'),
            icon: Settings
        },
        {
            title: 'Security',
            href: route('app.settings.password.edit'),
            icon: LockKeyhole
        }
    ])

    const marketingPrimaryItems = computed<NavItem[]>(() => {
        const items: NavItem[] = [
            {
                title: 'Home',
                href: route('marketing.home')
            }
        ]

        if (isAuthenticated.value) {
            items.push({
                title: 'Dashboard',
                href: route('app.dashboard')
            })

            if (isAdmin.value) {
                items.push({
                    title: 'Users',
                    href: route('app.admin.users.index')
                })
            }
        }

        return items
    })

    const marketingPrimaryAction = computed<NavItem>(() => {
        if (isAuthenticated.value) {
            return {
                title: 'Open dashboard',
                href: route('app.dashboard'),
                icon: LayoutGrid
            }
        }

        return {
            title: 'Get started',
            href: route('auth.register.create'),
            icon: UserPlus
        }
    })

    const marketingSecondaryAction = computed<NavItem>(() => {
        if (isAuthenticated.value) {
            return {
                title: 'Settings',
                href: route('app.settings.profile.edit'),
                icon: Settings
            }
        }

        return {
            title: 'Log in',
            href: route('auth.login.create'),
            icon: LogIn
        }
    })

    const marketingFooterGroups = computed<NavigationGroup[]>(() => {
        const platformItems: NavItem[] = [
            {
                title: 'Home',
                href: route('marketing.home')
            }
        ]

        if (isAuthenticated.value) {
            platformItems.push({
                title: 'Dashboard',
                href: route('app.dashboard')
            })
        }

        const accountItems: NavItem[] = isAuthenticated.value
            ? [
                  {
                      title: 'Profile settings',
                      href: route('app.settings.profile.edit')
                  },
                  {
                      title: 'Password settings',
                      href: route('app.settings.password.edit')
                  }
              ]
            : [
                  {
                      title: 'Log in',
                      href: route('auth.login.create')
                  },
                  {
                      title: 'Create account',
                      href: route('auth.register.create')
                  }
              ]

        const groups: NavigationGroup[] = [
            {
                title: 'Platform',
                items: platformItems
            },
            {
                title: 'Account',
                items: accountItems
            }
        ]

        if (isAdmin.value) {
            groups.push({
                title: 'Administration',
                items: [
                    {
                        title: 'User management',
                        href: route('app.admin.users.index')
                    }
                ]
            })
        }

        return groups
    })

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
