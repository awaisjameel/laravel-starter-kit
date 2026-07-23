import { createInertiaApp } from '@inertiajs/vue3'
import { createApp, createSSRApp, h } from 'vue'
import { createAppInstance } from './create-app'
import { configureRealtime } from './lib/realtime/config'
import type { AppPageProps } from './types'

const appName = import.meta.env.VITE_APP_NAME || 'Laravel'

// Lets Inertia tag the style elements it injects (progress bar, error modal)
// so they satisfy the nonce-based CSP set by `App\Http\Middleware\SecurityHeaders`.
const cspNonce = document.querySelector<HTMLMetaElement>('meta[name="csp-nonce"]')?.content ?? ''

configureRealtime()

createInertiaApp<AppPageProps>({
    // Resolved by `@inertiajs/vite`, which rewrites this literal into an
    // `import.meta.glob` resolver at build time, so it cannot be extracted
    // into a shared constant. Page names are module-relative, for example
    // `modules/users/pages/Index`.
    pages: {
        path: './modules',
        extension: '.vue',
        transform: (name) => name.replace(/^modules\//, '')
    },
    title: (title) => (title ? `${title} - ${appName}` : appName),
    nonce: cspNonce,
    setup({ el, App, props, plugin }) {
        if (el === null) {
            throw new Error('Inertia could not find its root element. Check the `@inertia` directive in `resources/views/app.blade.php`.')
        }

        // Inertia marks server-rendered roots with `data-server-rendered`. Hydrate
        // those and mount the rest, so toggling `INERTIA_SSR_ENABLED` never leaves
        // the client hydrating markup that was never rendered.
        const create = el.hasAttribute('data-server-rendered') ? createSSRApp : createApp

        createAppInstance({
            create,
            page: () => h(App, props),
            plugin
        }).mount(el)
    },
    progress: {
        color: '#4B5563'
    }
})
