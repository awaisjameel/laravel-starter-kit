# Frontend Rendering, SSR, And Hydration

Read before changing `resources/js/app.ts`, `resources/js/components/AppRoot.vue`, the root Blade view, stylesheet or font loading, the color scheme, the Content Security Policy, or any state or markup that renders on the server. How SSR is served is described in [runtime](runtime.md#server-side-rendering).

## Entry And App Setup

- `resources/js/app.ts` is the single entry for both the client and SSR, matching Inertia v3's default. `@inertiajs/vite` auto-detects it as the SSR entry, and `laravel()` names it as the `ssr` input so the stylesheet stays out of the SSR build. Do not reintroduce a separate `ssr.ts` or a manual `createServer` wrapper.
- `createInertiaApp(...)` must stay a bare top-level statement: the plugin rewrites it into the render function for the dev SSR endpoint and the production `createServer` boot. Its `pages` shorthand must stay an inline object literal, because a static AST transform compiles it into an `import.meta.glob` resolver over `resources/js/modules/**/*.vue`. The `transform` strips the leading `modules/` from the backend-supplied component name (`modules/users/pages/Index`).
- `setup` in `app.ts` builds the Vue app for both renders, so they cannot drift apart. It renders `resources/js/components/AppRoot.vue` around the Inertia page; `AppRoot` owns the global toaster and the [identity-driven cache reset](frontend-data.md#identity-changes). Register new plugins in `setup` and new root-level components in `AppRoot`.
- `setup` uses `createSSRApp` on the server and for roots Inertia marks with `data-server-rendered`, and `createApp` otherwise, so `INERTIA_SSR_ENABLED` can be toggled without a hydration mismatch.
- `setup` calls VueUse's `provideSSRWidth` with the desktop breakpoint. The server cannot measure a viewport, so media queries (the sidebar's mobile check) resolve against that width on the server and during hydration, then switch to the real viewport after mount. Without it, narrow screens hydrate a different sidebar tree and every later `useId()` shifts, breaking label and `aria-describedby` targets in production.
- Code at the top level of `app.ts` runs in both environments. Guard browser globals with `import.meta.env.SSR`, which Vite replaces at build time.
- The root Blade template uses `data-inertia` (not `inertia`) on head elements, per Inertia v3.

## Request Isolation

The SSR process is long-lived and serves concurrent requests, so a per-request value written to a module-level binding or `globalThis` is overwritten by whichever request rendered last. Pinia is therefore created per app instance in `setup`. The other module-level state follows the rule as described by its owner: [toasts](frontend.md#notifications), the [`useApiQuery` cache](frontend-data.md#api-requests), and the [`useAppearance` override](#color-scheme).

## Deterministic Markup

`Math.random()`, `Date.now()`, and `crypto.randomUUID()` produce one value in the SSR process and a different one during hydration, so Vue reports a mismatch and the server's value is the one that stays in the DOM. Use `useId()` for generated element ids and `url(#…)` references (`PlaceholderPattern`, `SidebarMenuSkeleton`); it is stable across both renders. This applies to vendored `components/ui/**` primitives too — upstream shadcn-vue does not assume SSR.

## Stylesheets And Fonts

- `resources/css/app.css` is a Vite entry of its own (`vite.config.ts` `input`, first in the `@vite` array) and must never be imported from `app.ts`. SSR ships a fully rendered document, so the browser paints as soon as the HTML lands; CSS reaching it through the JS module graph would render that markup unstyled and reflow when the bundle evaluated. As a separate entry it is a render-blocking `<link rel="stylesheet">` in dev and production alike — the Vite dev server serves it as real `text/css` because a `<link>` sends `Accept: text/css`.
- Fonts are self-hosted through laravel-vite-plugin's `fonts` option in `vite.config.ts`, which is the only place a font family is declared. Instrument Sans ships as one variable latin woff2 (weights 400–700) read from the locked `@fontsource-variable/instrument-sans` package, so builds need no network. `@fonts` in the root view preloads it (also sent as an HTTP `Link` header) and inlines the `@font-face` rules under the CSP nonce. The optional `fontaine` peer generates a metric-matched `Instrument Sans fallback` face so text does not shift when the font arrives. `theme.css` maps Tailwind's `--font-sans` to the generated `--font-instrument-sans` variable, which already carries the fallback stack. To add a family, add a definition there and reference its variable from `theme.css`; never link a third-party font stylesheet.

## Color Scheme

The color scheme is server-rendered. `HandleAppearance` normalizes the `appearance` cookie through `App\Enums\Appearance` and shares it with the root view, which puts `class="dark"` on `<html>` directly; there is no boot script and nothing for the client to re-apply. `HandleInertiaRequests` shares the same value as a page prop so `useAppearance` seeds the appearance UI from it and hydrates without a mismatch. `useAppearance` holds only a client override at module scope (`null` until the visitor toggles, written solely from a browser interaction), which is what keeps this module-level state out of the shared SSR process.

## Content Security Policy

- `SecurityHeaders` generates the per-request CSP nonce with Laravel's `Vite::useCspNonce()`, so `@vite` adds it to every tag it renders. The root view exposes `Vite::cspNonce()` in a `meta[name="csp-nonce"]` tag, and `app.ts` passes it to Inertia so its injected style elements satisfy the policy.
- `SecurityHeaders` allows the `http:`/`https:` scheme sources in `style-src` and `font-src` only when `App::isLocal()`, because the dev stylesheet and fonts are served by the Vite dev server origin rather than `'self'`. Outside local, every asset, fonts included, is same-origin and `<style>` elements require the nonce. `style-src-attr 'unsafe-inline'` applies everywhere: Vue renders `v-show` and bound styles (such as the sidebar's width variables) as `style` attributes in server-rendered markup, a nonce cannot cover an attribute, and attributes cannot execute script.
- The full header set is listed in [routes and security](routes-and-security.md#security-rules).
