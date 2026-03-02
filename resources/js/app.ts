import '../css/app.css'

import { createInertiaApp } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { createPinia } from 'pinia'
import type { DefineComponent } from 'vue'
import { createApp, h } from 'vue'
import { route as ziggyRoute, ZiggyVue } from 'ziggy-js'
import { initializeTheme } from './composables/useAppearance'
import type { AppPageProps } from './types'

const appName = import.meta.env.VITE_APP_NAME || 'Laravel'

const pinia = createPinia()

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) => resolvePageComponent(`./${name}.vue`, import.meta.glob<DefineComponent>('./modules/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const ziggy = props.initialPage.props.ziggy as AppPageProps['ziggy']
        const routeHelper = ((...args: unknown[]) => {
            if (args.length === 0) {
                return ziggyRoute()
            }

            return ziggyRoute(
                args[0] as Parameters<typeof ziggyRoute>[0],
                args[1] as Parameters<typeof ziggyRoute>[1],
                args[2] as Parameters<typeof ziggyRoute>[2],
                (args[3] as Parameters<typeof ziggyRoute>[3] | undefined) ?? ziggy
            )
        }) as unknown as typeof ziggyRoute

        ;(globalThis as typeof globalThis & { route: typeof ziggyRoute }).route = routeHelper

        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue, {
                ...ziggy,
                location: new URL(ziggy.location)
            })
            .use(pinia)
            .mount(el)
    },
    progress: {
        color: '#4B5563'
    }
})

// This will set light / dark mode on page load...
initializeTheme()
