import { route } from 'ziggy-js'

// `ZiggyVue` installs `route()` onto the app instance (global property + provide),
// which keeps it request-scoped under SSR. There is deliberately no `globalThis`
// binding: in the long-lived SSR process a global would be overwritten by whichever
// request rendered last. Prefer the generated Wayfinder helpers in application code.
declare module '@vue/runtime-core' {
    interface ComponentCustomProperties {
        route: typeof route
    }
}
