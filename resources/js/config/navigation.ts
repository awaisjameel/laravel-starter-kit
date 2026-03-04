import appRoutes from '@/routes/app'
import authRoutes from '@/routes/auth'
import marketingRoutes from '@/routes/marketing'
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
    marketingHome: () => marketingRoutes.home.url(),
    appDashboard: () => appRoutes.dashboard.url(),
    adminUsers: () => appRoutes.admin.users.index.url(),
    settingsProfile: () => appRoutes.settings.profile.edit.url(),
    settingsPassword: () => appRoutes.settings.password.edit.url(),
    settingsAppearance: () => appRoutes.settings.appearance.url(),
    authRegister: () => authRoutes.register.create.url(),
    authLogin: () => authRoutes.login.create.url()
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
        href: navRoutes.settingsAppearance()
    }
]

export const buildDashboardPrimaryItems = (context: NavigationContext): NavItem[] => {
    const items: NavItem[] = [
        {
            title: 'Dashboard',
            href: navRoutes.appDashboard(),
            activeMatch: 'prefix',
            icon: LayoutGrid
        }
    ]

    if (context.isAdmin) {
        items.push({
            title: 'Users',
            href: navRoutes.adminUsers(),
            activeMatch: 'prefix',
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
            href: navRoutes.appDashboard(),
            activeMatch: 'prefix'
        })

        if (context.isAdmin) {
            items.push({
                title: 'Users',
                href: navRoutes.adminUsers(),
                activeMatch: 'prefix'
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
            activeMatch: 'prefix',
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
            href: navRoutes.appDashboard(),
            activeMatch: 'prefix'
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
                    href: navRoutes.adminUsers(),
                    activeMatch: 'prefix'
                }
            ]
        })
    }

    return groups
}
