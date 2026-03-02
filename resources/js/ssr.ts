import { createInertiaApp } from '@inertiajs/vue3'
import createServer from '@inertiajs/vue3/server'
import { renderToString } from '@vue/server-renderer'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { createSSRApp, DefineComponent, h } from 'vue'
import { route as ziggyRoute, ZiggyVue } from 'ziggy-js'

const appName = import.meta.env.VITE_APP_NAME || 'Laravel'

createServer(
    (page) =>
        createInertiaApp({
            page,
            render: renderToString,
            title: (title) => `${title} - ${appName}`,
            resolve: resolvePage,
            setup: ({ App, props, plugin }) => {
                const ziggy = {
                    ...page.props.ziggy,
                    location: new URL(page.props.ziggy.location)
                }
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

                return createSSRApp({ render: () => h(App, props) })
                    .use(plugin)
                    .use(ZiggyVue, ziggy)
            }
        }),
    { cluster: true }
)

function resolvePage(name: string) {
    const pages = import.meta.glob<DefineComponent>('./modules/**/*.vue')

    return resolvePageComponent<DefineComponent>(`./${name}.vue`, pages)
}
