import { createPinia } from 'pinia'
import type { CreateAppFunction, Plugin, VNode, App as VueApp } from 'vue'
import { Fragment, h } from 'vue'
import AppToaster from './components/base/toast/AppToaster.vue'

interface CreateAppInstanceOptions {
    create: CreateAppFunction<Element>
    page: () => VNode
    plugin: Plugin
}

// Single source of truth for the root tree and the plugins both entries install,
// so the client and the server render the same component tree and hydration
// cannot drift. Everything request-scoped is bound per instance because the SSR
// process is long-lived: Pinia must not share store state between requests.
export const createAppInstance = ({ create, page, plugin }: CreateAppInstanceOptions): VueApp =>
    create({ render: () => h(Fragment, [page(), h(AppToaster)]) })
        .use(plugin)
        .use(createPinia())
