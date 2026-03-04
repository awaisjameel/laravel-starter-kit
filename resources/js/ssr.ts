import { createInertiaApp } from '@inertiajs/vue3'
import createServer from '@inertiajs/vue3/server'
import { renderToString } from '@vue/server-renderer'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { createSSRApp, DefineComponent, h } from 'vue'
import { ZiggyVue } from 'ziggy-js'
import type { AppPageProps } from './types'
import { bindGlobalRouteHelper, toZiggyVueConfig } from './utils/ziggy'

const appName = import.meta.env.VITE_APP_NAME || 'Laravel'

createServer(
    (page) =>
        createInertiaApp({
            page,
            render: renderToString,
            title: (title) => `${title} - ${appName}`,
            resolve: resolvePage,
            setup: ({ App, props, plugin }) => {
                const ziggy = page.props.ziggy as AppPageProps['ziggy']
                bindGlobalRouteHelper(ziggy)

                return createSSRApp({ render: () => h(App, props) })
                    .use(plugin)
                    .use(ZiggyVue, toZiggyVueConfig(ziggy))
            }
        }),
    { cluster: true }
)

function resolvePage(name: string) {
    const pages = import.meta.glob<DefineComponent>('./modules/**/*.vue')

    return resolvePageComponent<DefineComponent>(`./${name}.vue`, pages)
}
