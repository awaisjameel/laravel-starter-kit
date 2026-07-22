import { createInertiaApp } from '@inertiajs/vue3'
import { createSSRApp, h } from 'vue'
import { createAppInstance } from './create-app'
import { configureRealtime } from './lib/realtime/config'
import type { AppPageProps } from './types'

const appName = import.meta.env.VITE_APP_NAME || 'Laravel'

// Resolves to the `null` broadcaster during SSR, so pages using realtime
// composables render on the server without opening any connection.
configureRealtime()

// `@inertiajs/vite` wraps this call: it exports the render function for the
// dev-server SSR endpoint and boots `createServer` for production builds.
createInertiaApp<AppPageProps>({
    // Rewritten into an `import.meta.glob` resolver by `@inertiajs/vite`, so
    // this literal has to stay inline here as well as in `app.ts`.
    pages: {
        path: './modules',
        extension: '.vue',
        transform: (name) => name.replace(/^modules\//, '')
    },
    title: (title) => (title ? `${title} - ${appName}` : appName),
    setup: ({ App, props, plugin }) =>
        createAppInstance({
            create: createSSRApp,
            page: () => h(App, props),
            plugin
        })
})
