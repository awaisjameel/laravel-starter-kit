import { createPinia } from 'pinia'
import type { CreateAppFunction, Plugin, VNode, App as VueApp } from 'vue'
import { Fragment, h } from 'vue'
import { ZiggyVue } from 'ziggy-js'
import AppToaster from './components/base/toast/AppToaster.vue'
import type { AppPageProps } from './types'
import { bindGlobalRouteHelper, toZiggyVueConfig } from './utils/ziggy'

interface CreateAppInstanceOptions {
    create: CreateAppFunction<Element>
    page: () => VNode
    plugin: Plugin
    ziggy: AppPageProps['ziggy']
}

// Single source of truth for the root tree and the plugins both entries install,
// so the client and the server render the same component tree and hydration
// cannot drift. Pinia is created per instance because the SSR process is
// long-lived and must not share store state between requests.
export const createAppInstance = ({ create, page, plugin, ziggy }: CreateAppInstanceOptions): VueApp => {
    bindGlobalRouteHelper(ziggy)

    return create({ render: () => h(Fragment, [page(), h(AppToaster)]) })
        .use(plugin)
        .use(ZiggyVue, toZiggyVueConfig(ziggy))
        .use(createPinia())
}
