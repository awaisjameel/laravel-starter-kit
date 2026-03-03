import type { NavItem } from '@/types'
import { LayoutGrid, LockKeyhole, LogIn, Settings, UserPlus, Users } from 'lucide-vue-next'

export interface NavigationContext {
    isAuthenticated: boolean
    isAdmin: boolean
}

export interface NavigationGroup {
    title: string
    items: NavItem[]
}

const navRoutes = {
    marketingHome: () => route('marketing.home'),
    appDashboard: () => route('app.dashboard'),
    adminUsers: () => route('app.admin.users.index'),
    settingsProfile: () => route('app.settings.profile.edit'),
    settingsPassword: () => route('app.settings.password.edit'),
    authRegister: () => route('auth.register.create'),
    authLogin: () => route('auth.login.create')
}

export const buildSettingsNavItems = (): NavItem[] => [
    {
        title: 'Profile',
        href: navRoutes.settingsProfile()
    },
    {
        title: 'Password',
        href: navRoutes.settingsPassword()
    },
    {
        title: 'Appearance',
        href: route('app.settings.appearance')
    }
]

export const buildDashboardPrimaryItems = (context: NavigationContext): NavItem[] => {
    const items: NavItem[] = [
        {
            title: 'Dashboard',
            href: navRoutes.appDashboard(),
            icon: LayoutGrid
        }
    ]

    if (context.isAdmin) {
        items.push({
            title: 'Users',
            href: navRoutes.adminUsers(),
            icon: Users
        })
    }

    return items
}

export const buildDashboardFooterItems = (): NavItem[] => [
    {
        title: 'Settings',
        href: navRoutes.settingsProfile(),
        icon: Settings
    },
    {
        title: 'Security',
        href: navRoutes.settingsPassword(),
        icon: LockKeyhole
    }
]

export const buildMarketingPrimaryItems = (context: NavigationContext): NavItem[] => {
    const items: NavItem[] = [
        {
            title: 'Home',
            href: navRoutes.marketingHome()
        }
    ]

    if (context.isAuthenticated) {
        items.push({
            title: 'Dashboard',
            href: navRoutes.appDashboard()
        })

        if (context.isAdmin) {
            items.push({
                title: 'Users',
                href: navRoutes.adminUsers()
            })
        }
    }

    return items
}

export const buildMarketingPrimaryAction = (context: NavigationContext): NavItem => {
    if (context.isAuthenticated) {
        return {
            title: 'Open dashboard',
            href: navRoutes.appDashboard(),
            icon: LayoutGrid
        }
    }

    return {
        title: 'Get started',
        href: navRoutes.authRegister(),
        icon: UserPlus
    }
}

export const buildMarketingSecondaryAction = (context: NavigationContext): NavItem => {
    if (context.isAuthenticated) {
        return {
            title: 'Settings',
            href: navRoutes.settingsProfile(),
            icon: Settings
        }
    }

    return {
        title: 'Log in',
        href: navRoutes.authLogin(),
        icon: LogIn
    }
}

export const buildMarketingFooterGroups = (context: NavigationContext): NavigationGroup[] => {
    const platformItems: NavItem[] = [
        {
            title: 'Home',
            href: navRoutes.marketingHome()
        }
    ]

    if (context.isAuthenticated) {
        platformItems.push({
            title: 'Dashboard',
            href: navRoutes.appDashboard()
        })
    }

    const accountItems: NavItem[] = context.isAuthenticated
        ? [
              {
                  title: 'Profile settings',
                  href: navRoutes.settingsProfile()
              },
              {
                  title: 'Password settings',
                  href: navRoutes.settingsPassword()
              }
          ]
        : [
              {
                  title: 'Log in',
                  href: navRoutes.authLogin()
              },
              {
                  title: 'Create account',
                  href: navRoutes.authRegister()
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

    if (context.isAdmin) {
        groups.push({
            title: 'Administration',
            items: [
                {
                    title: 'User management',
                    href: navRoutes.adminUsers()
                }
            ]
        })
    }

    return groups
}
