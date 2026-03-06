import '../css/app.css'

import { createInertiaApp } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { createPinia } from 'pinia'
import type { DefineComponent } from 'vue'
import { createApp, Fragment, h } from 'vue'
import { ZiggyVue } from 'ziggy-js'
import AppToaster from './components/base/toast/AppToaster.vue'
import { initializeTheme } from './composables/useAppearance'
import { configureRealtime } from './lib/realtime/config'
import type { AppPageProps } from './types'
import { bindGlobalRouteHelper, toZiggyVueConfig } from './utils/ziggy'

const appName = import.meta.env.VITE_APP_NAME || 'Laravel'

const pinia = createPinia()

configureRealtime()

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) => resolvePageComponent(`./${name}.vue`, import.meta.glob<DefineComponent>('./modules/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const ziggy = props.initialPage.props.ziggy as AppPageProps['ziggy']
        bindGlobalRouteHelper(ziggy)

        createApp({ render: () => h(Fragment, [h(App, props), h(AppToaster)]) })
            .use(plugin)
            .use(ZiggyVue, toZiggyVueConfig(ziggy))
            .use(pinia)
            .mount(el)
    },
    progress: {
        color: '#4B5563'
    }
})

// This will set light / dark mode on page load...
initializeTheme()
