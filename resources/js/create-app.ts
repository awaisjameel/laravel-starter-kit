import { createPinia } from 'pinia'
import type { CreateAppFunction, Plugin, VNode, App as VueApp } from 'vue'
import { Fragment, h } from 'vue'
import { ZiggyVue } from 'ziggy-js'
import AppToaster from './components/base/toast/AppToaster.vue'
import type { AppPageProps } from './types'
import { toZiggyVueConfig } from './utils/ziggy'

interface CreateAppInstanceOptions {
    create: CreateAppFunction<Element>
    page: () => VNode
    plugin: Plugin
    ziggy: AppPageProps['ziggy']
}

// Single source of truth for the root tree and the plugins both entries install,
// so the client and the server render the same component tree and hydration
// cannot drift. Everything request-scoped is bound per instance because the SSR
// process is long-lived: Pinia must not share store state between requests, and
// `ZiggyVue` keeps `route()` on this app rather than on a shared global.
export const createAppInstance = ({ create, page, plugin, ziggy }: CreateAppInstanceOptions): VueApp =>
    create({ render: () => h(Fragment, [page(), h(AppToaster)]) })
        .use(plugin)
        .use(ZiggyVue, toZiggyVueConfig(ziggy))
        .use(createPinia())
