import { afterEach, expect, it, vi } from 'vitest'
import { createApp, defineComponent, h, reactive } from 'vue'
import { clearApiQueryCache, getApiQueryCacheData, setApiQueryCacheData } from '../composables/useApiQuery'
import { createAppInstance } from '../create-app'

const pageProps = reactive<{ auth: { user: { id: number } | null }; flash: { message: null; error: null; status: null } }>({
    auth: { user: { id: 1 } },
    flash: { message: null, error: null, status: null }
})
const state = reactive<{ props: typeof pageProps | undefined }>({ props: undefined })
vi.mock('@inertiajs/vue3', () => ({ usePage: () => state }))

afterEach(() => clearApiQueryCache())

it('clears cached account data synchronously on an identity change', () => {
    const element = document.createElement('div')
    const Page = defineComponent({
        setup() {
            state.props = pageProps
            return () => h('div')
        }
    })
    const app = createAppInstance({ create: createApp, page: () => h(Page), plugin: { install: () => undefined } })
    app.mount(element)
    try {
        setApiQueryCacheData('account', { private: 'first-user' })
        pageProps.auth.user = null
        expect(getApiQueryCacheData('account')).toBeUndefined()
        pageProps.auth.user = { id: 2 }
        setApiQueryCacheData('account', { private: 'second-user' })
        pageProps.auth.user = { id: 3 }
        expect(getApiQueryCacheData('account')).toBeUndefined()
    } finally {
        app.unmount()
    }
})
