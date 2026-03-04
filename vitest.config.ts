import { fileURLToPath, URL } from 'node:url'
import vue from '@vitejs/plugin-vue'
import AutoImport from 'unplugin-auto-import/vite'
import { defineConfig } from 'vitest/config'

export default defineConfig({
    plugins: [
        vue(),
        AutoImport({
            dts: false,
            imports: [
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
            ],
            dirs: ['resources/js/composables/**', 'resources/js/stores/**', 'resources/js/lib/**', 'resources/js/utils/**']
        })
    ],
    resolve: {
        alias: {
            '@': fileURLToPath(new URL('./resources/js', import.meta.url))
        }
    },
    test: {
        environment: 'jsdom',
        include: ['resources/js/**/*.test.ts']
    }
})
