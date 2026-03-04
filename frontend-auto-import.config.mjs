export const autoImportMessage =
    'Manual import is not needed here. This project auto-imports this symbol; remove the import and rely on auto-import.'

export const autoImportDirs = ['resources/js/composables/**', 'resources/js/stores/**', 'resources/js/lib/**', 'resources/js/utils/**']

export const autoImportImports = [
    'vue',
    'vue-router',
    {
        '@inertiajs/vue3': ['usePage', 'useForm', 'useRemember', 'usePoll', 'router', 'Deferred'],
        '@inertiajs/core': ['Method'],
        'ziggy-js': ['Ziggy'],
        '@/routes/app': [['default', 'appRoutes']],
        '@/routes/auth': [['default', 'authRoutes']],
        '@/routes/marketing': [['default', 'marketingRoutes']],
        '@/config/breadcrumbs': [
            'buildDashboardBreadcrumbs',
            'buildUsersBreadcrumbs',
            'buildSettingsProfileBreadcrumbs',
            'buildSettingsPasswordBreadcrumbs',
            'buildSettingsAppearanceBreadcrumbs'
        ]
    }
]

export const autoImportRestrictedPaths = [
    {
        name: '@inertiajs/vue3',
        importNames: ['usePage', 'useForm', 'useRemember', 'usePoll', 'router', 'Deferred', 'Head', 'Link'],
        message: autoImportMessage
    },
    {
        name: 'ziggy-js',
        importNames: ['Ziggy'],
        message: autoImportMessage
    },
    {
        name: '@/composables/useAppPage',
        importNames: ['useAppPage', 'useAuthUser'],
        message: autoImportMessage
    },
    {
        name: '@/routes/app',
        importNames: ['default'],
        message: autoImportMessage
    },
    {
        name: '@/routes/auth',
        importNames: ['default'],
        message: autoImportMessage
    },
    {
        name: '@/routes/marketing',
        importNames: ['default'],
        message: autoImportMessage
    },
    {
        name: '@/config/breadcrumbs',
        importNames: [
            'buildDashboardBreadcrumbs',
            'buildUsersBreadcrumbs',
            'buildSettingsProfileBreadcrumbs',
            'buildSettingsPasswordBreadcrumbs',
            'buildSettingsAppearanceBreadcrumbs'
        ],
        message: autoImportMessage
    }
]

export const autoImportRestrictedPatterns = [
    {
        group: ['@/composables/*', '@/composables/**'],
        message: autoImportMessage
    },
    {
        group: ['@/stores/*', '@/stores/**'],
        message: autoImportMessage
    },
    {
        group: ['@/components/**/*.vue', '@/layouts/**/*.vue'],
        message: autoImportMessage
    }
]
