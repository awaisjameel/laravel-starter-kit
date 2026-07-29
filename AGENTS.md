# AGENTS.md

## Purpose

This repository is a modular Laravel + Inertia + Vue starter kit with backend-owned contracts, strict typing, realtime support, and generator-backed conventions.
The backend is the source of truth for DTOs, enums, route/action helpers, and realtime contracts.
Agents must keep the codebase internally consistent, remove redundancy, and deliver complete, production-quality implementations with no partial work left behind.

## Source Of Truth Hierarchy

When guidance conflicts, resolve it in this order:

1. Current runtime behavior and code in the repository.
2. Generated artifacts that are owned by backend contracts.
3. `AGENTS.md`.
4. Secondary docs such as `README.md` and files under `docs/**`.

If code and docs disagree, trust the code, then fix the docs in the same task when relevant.

## Non-Negotiable Engineering Bar

- Think through the full impact of every change before editing code.
- Verify the current implementation first; never preserve stale assumptions from docs, habits, or previous starter-kit conventions.
- Understand the full requirement and the full existing implementation before changing anything; do not optimize locally while missing a broader contract, dependency, or workflow constraint.
- Prefer the simplest design that is correct, extensible, testable, and aligned with existing project patterns.
- Continuously align the codebase to global patterns, standards, and best practices across backend, frontend, generated artifacts, and tests instead of letting module-local exceptions accumulate.
- Every meaningful change should reduce mismatch, incomplete logic, duplicate behavior, weak typing, and ad hoc structure where it touches the system.
- Structure code for maximum cleanliness, reusability, maintainability, strict type safety, extensibility, performance, modularity, robustness, and long-term developer experience.
- Prefer the solution with the fewest necessary touch points and the strongest shared abstraction, but never at the cost of correctness, clarity, or alignment with current architecture.
- Preserve working functionality while refactoring. Cleanup is expected to improve consistency and safety without introducing regressions or speculative rewrites.
- Maintain excellent DI: prefer constructor injection, readonly dependencies, explicit typed inputs, and thin transport layers.
- Maintain excellent DX: preserve predictable file placement, generated contract flows, route/action helpers, and shared abstractions instead of ad hoc patterns.
- Maintain excellent performance: avoid duplicate scans, redundant queries, unnecessary rerenders, unnecessary network calls, and unnecessary abstractions.
- Remove dead code, duplicate logic, stale comments, unused scaffolding, and compatibility shims when they are no longer justified.
- Never leave a feature half-finished. If a change requires backend, frontend, generated artifacts, tests, docs, or cleanup, complete all of them in the same task.
- Match the existing architecture precisely. If the architecture must change, update this file in the same change.
- No unnecessary code comments. Code should be self-documenting. Use comments only to explain why something is done, not what is done.

## Mandatory Workflow

1. Inspect the existing implementation, affected modules, related tests, and generated-contract flow before making changes.
2. Confirm the actual requirement, invariants, and user-visible behavior that must remain correct before choosing an implementation approach.
3. Reuse existing module services, requests, DTOs, queries, commands, handlers, responders, composables, and base UI primitives before introducing anything new.
4. Choose the most efficient clean change that improves global consistency and minimizes future touch points, not just the shortest local patch.
5. Keep changes focused, typed, and module-local unless the concern is genuinely cross-cutting.
6. Regenerate artifacts whenever backend-owned contracts, routes, channels, providers, listeners, or enums change.
7. Run the required quality gates after every change and treat `composer generate-and-cleanup` as a mandatory zero-error, zero-warning completion gate.
8. Update `AGENTS.md` whenever architecture, workflows, or enforcement rules change.
9. Do not stop at analysis. Deliver the finished implementation, verification, and cleanup unless the user explicitly redirects you.

## Global Alignment Standard

- Backend, frontend, generated contracts, tests, and docs must converge on one consistent canonical implementation for each concern.
- Do not leave known mismatch, duplicate semantics, inconsistent naming, incomplete logic, or parallel contract shapes in place when the current task touches them.
- Prefer direct cleanup over compatibility shims unless persisted data or external consumers require an explicit transition.
- Use backend-owned DTOs, enums, route helpers, realtime payloads, and generated contracts as the canonical contract surface. Do not redefine them manually in frontend or feature-local code.
- Shared concerns must use shared abstractions. Module-specific concerns must remain module-local. Do not duplicate cross-cutting behavior in feature modules and do not prematurely move feature-specific logic into shared layers.
- Refactors must improve consistency across modules, not create a stronger pattern in one module while leaving newly introduced divergence elsewhere in the touched scope.
- Any cleanup that changes structure must keep strict typing intact end to end: request DTOs, handlers, resources, generated types, composables, page props, tests, and runtime parsing.
- When choosing between alternatives, prefer the one that most clearly strengthens long-term maintainability, explicit contracts, and safe extension paths.

## Agent Workflow Protocol (MANDATORY)

Before writing ANY code, agents MUST follow this protocol:

1. Understanding Phase (REQUIRED)
    - Read First: Never assume. Read all related files, understand the full context.
    - Trace Dependencies: Map all files, services, events, and components that will be affected.
    - Validate Requirements: Confirm the exact requirement, expected runtime behavior, generated-contract implications, and non-functional constraints before designing a change.
2. Planning Phase (REQUIRED)
    - Design First: Outline the solution architecture before coding.
    - Impact Analysis: List all files that need changes (backend, frontend, tests, docs).
    - Alignment Check: Compare the intended change to existing global patterns and identify any touched mismatch, duplicate logic, or incomplete contract that should be cleaned up in the same pass.
3. Implementation Phase
    - One Change at a Time: Make focused, atomic changes.
    - Follow Patterns: Match existing conventions exactly.
    - Raise The Bar: Leave touched code more consistent, more strictly typed, and less duplicated than you found it.
4. Verification Phase
    - Run Quality Gate: `composer generate-and-cleanup` after every change.
    - Zero-Warning Rule: Do not consider the task complete while `composer generate-and-cleanup` reports errors or warnings that can be resolved within the repository.
5. Completion Criteria
    - All tests pass, no dead code/TODOs remain, and `AGENTS.md` is updated if applicable.

## Change-Impact Checklist

For every non-trivial change, explicitly verify all affected layers before considering the task complete:

- backend contracts
- generated artifacts
- frontend consumers
- route/action helper usage
- authorization and policies/gates
- realtime contracts and subscriptions
- database schema, indexes, and query behavior
- tests
- docs
- stale code removal

## Current Stack

- PHP: `^8.4`
- Laravel: `^13.21`
- Inertia Laravel: `^3.1`
- Inertia client (`@inertiajs/vue3`, `@inertiajs/vite`): `^3.6`
- Reverb: `^1.11`
- Sanctum: `^4.3.3`
- Wayfinder: `^0.1.20` (sole route surface; Ziggy is deliberately not installed)
- Spatie Laravel Data: `^4.23`
- Spatie TypeScript Transformer: `^3.3`
- Pest: `^5.0` with Laravel and PHPStan plugins `^5.0` (PHPUnit 13 engine)
- PHPStan/Larastan: `^2.2` / `^3.10` at `max` level with official strict and deprecation rules
- Rector: `^2.5`
- Vue: `^3.5.40`
- TypeScript: `^6.0`
- Vite: `^8.1` (Rolldown bundler)
- `laravel-vite-plugin`: `^3.1`
- Tailwind CSS: `^4.3`
- Pinia: `^4.0`
- Icons: `@lucide/vue` `^1.25` (the `lucide-vue-next` package is deprecated)
- Node: `>=24.1.0`
- npm: `>=11.2.1`
- Package manager: `npm` ONLY. This is enforced by `ensure-node-env.js`. Do not use yarn, pnpm, or bun.
- `composer.lock` and `package-lock.json` are committed application contracts; CI and local reproducible installs must honor them.
- Published Sail Docker contexts are limited to `docker/8.4` and `docker/8.5`, matching the Composer PHP constraint.

### Version Constraints Worth Knowing

- TypeScript must stay on `6.x`. TypeScript 7 (the native compiler) has no stable programmatic API yet, so `vue-tsc`/Volar cannot use it and `typescript-eslint` caps at `<6.1.0`.
- `concurrently` 10 pins a vulnerable `shell-quote`. `package.json` carries an `overrides` entry forcing `shell-quote ^1.10.0`; drop it once upstream repins.
- `@vue/test-utils` currently pins `js-beautify` 1, whose `glob` dependency is deprecated. `package.json` overrides `glob` to `^12.0.0`; remove the override once Test Utils updates its formatter dependency.
- `optionalDependencies` pin the Linux x64 native binaries used by CI/Docker. Vite 8 bundles with Rolldown, so the binding is `@rolldown/binding-linux-x64-gnu` (not `@rollup/rollup-*`).
- Those three entries must use exact versions that match the resolved core packages (`rolldown`, `lightningcss`, `@tailwindcss/oxide`). A caret range can hoist a newer binding than the core package expects and break the Linux build. Re-check them after any Vite or Tailwind bump.

## Current Runtime And Tooling

- `composer dev` starts:
    - `php artisan serve`
    - `php artisan queue:listen --queue=realtime,high,default --tries=1`
    - `php artisan pail --timeout=0`
    - `npm run dev`
    - `php artisan reverb:start --host=0.0.0.0 --port=8080 --hostname=127.0.0.1 --no-interaction`
- `composer dev:ssr` builds SSR assets, then starts:
    - `php artisan serve`
    - `php artisan queue:listen --queue=realtime,high,default --tries=1`
    - `php artisan pail --timeout=0`
    - `php artisan inertia:start-ssr`
    - `php artisan reverb:start --host=0.0.0.0 --port=8080 --hostname=127.0.0.1 --no-interaction`
- `pm2.config.cjs` currently manages production-style queue workers, Reverb, the Inertia SSR server, and the scheduler.
- SSR is served two different ways and both are wired up:
    - Development: `@inertiajs/vite` exposes `/__inertia_ssr` on the Vite dev server and `inertia-laravel` routes to it automatically while Vite is hot. `composer dev` therefore renders pages server-side with HMR and no extra process.
    - Production / `composer dev:ssr`: `npm run build:ssr` emits `bootstrap/ssr/ssr.js` and `php artisan inertia:start-ssr` serves it.
- `INERTIA_SSR_ENABLED` in `.env.example` toggles both paths.

## Canonical Architecture

### Backend

- Application bootstrapping is defined in `bootstrap/app.php`.
- `bootstrap/providers.php` only lists:
    - `App\Providers\AppServiceProvider`
    - `App\Providers\AuthServiceProvider`
    - `App\Providers\TypeScriptTransformerServiceProvider`
- Module providers are not added there manually; they are auto-registered during app boot through `App\Modules\Shared\Support\ModuleRegistry::providerClasses(...)`.
- Web middleware appended in `bootstrap/app.php`:
    - `App\Http\Middleware\HandleAppearance`
    - `App\Http\Middleware\HandleInertiaRequests`
    - `App\Http\Middleware\SecurityHeaders`
    - `Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets`
- Guest redirects are configured centrally in `bootstrap/app.php` to `route('auth.login.create')`.
- Events are registered from `app/Listeners` and module-discovered listener directories via `ModuleRegistry::listenerDirectories(...)`.

### Module Discovery

- Canonical discovery source: `app/Modules/Shared/Support/ModuleRegistry.php`
- Cache version: `3` (auto-invalidated when version changes)
- Cached manifest path: `bootstrap/cache/modules.php`
- Discovery payload currently includes:
    - web routes
    - api routes
    - gate files
    - policy files
    - channel files
    - listener directories
    - module provider files
- Current discovery priority order:
    - web routes: `Marketing`, `Auth`, `Dashboard`, `Settings`, `Users`
    - api routes: `Api/V1`
    - gate files: `Users`
    - policy files: `Users`
    - channel files: `Shared`, `Users`
    - listener directories: `Users`
    - provider files: no explicit priority list
- Production HTTP runtime trusts the cached manifest when available.
- Console and non-production runtimes can rescan when the manifest is missing or stale.
- Do not introduce separate ad hoc route, gate, channel, listener, or provider discovery logic outside the registry unless the architecture is intentionally changed and this file is updated.

### Current Module Layout

- Module root: `app/Modules`
- Current modules:
    - `Marketing` - Public marketing pages
    - `Auth` - Authentication (login, register, password reset, email verification)
    - `Dashboard` - Authenticated app dashboard
    - `Settings` - User settings (profile, password, appearance)
    - `Users` - Admin user management with realtime
    - `Api/V1` - API v1 endpoints
    - `Shared` - Shared infrastructure and utilities
- Shared domain primitives remain in:
    - `app/Models/User.php`
    - `app/Enums/UserRole.php`

### Backend Ownership Rules

- Module-specific backend code must stay under `app/Modules/<Module>/**`.
- Keep these inside the owning module:
    - controllers
    - requests
    - data DTOs
    - queries
    - commands
    - handlers
    - policies
    - gates
    - broadcast events
    - notifications
    - channel callbacks
    - listeners
    - support classes
    - resources
    - manifests
    - events (domain and broadcast)
- Only truly shared domain primitives belong in `app/Models/**` and `app/Enums/**`.
- If reuse spans multiple modules, move the code to `app/Modules/Shared/**` instead of duplicating it.
- Do not place module-only code under global shared locations.
- Backend event listeners under `app/Modules/**/Listeners/**` are auto-discovered through Laravel event discovery. Do not manually register module listener classes in `AppServiceProvider`.

### Shared Backend Infrastructure

- Shared page responder: `app/Modules/Shared/Http/Responders/PageResponder.php`
- Shared API responder: `app/Modules/Shared/Http/Responders/ApiResponder.php`
- Shared request DTO base classes:
    - `app/Modules/Shared/Http/Requests/DataRequest.php`
    - `app/Modules/Shared/Http/Requests/DataFormRequest.php`
    - `app/Modules/Shared/Http/Requests/DataQueryRequest.php`
- Shared authenticated actor resolver:
    - `app/Modules/Shared/Auth/RequestActor.php`
- Shared mutation context infrastructure:
    - `app/Modules/Shared/Mutations/MutationMetadata.php`
    - `app/Modules/Shared/Mutations/MutationContext.php`
- Shared realtime infrastructure:
    - `app/Modules/Shared/Realtime/**`
    - `app/Modules/Shared/Enums/SharedRealtimeChannel.php`
    - `app/Modules/Shared/Routes/channels.php`
- Current auto-discovered shared module provider:
    - `app/Modules/Shared/Providers/ModuleServiceProvider.php`

### Request / Controller / Handler Conventions

- Every PHP file must declare `strict_types=1`.
- Transport-layer input must be typed through request DTOs.
- Prefer extending `DataFormRequest` for validated form payloads.
- Prefer extending `DataQueryRequest` for query payloads so trimming, defaults, pagination, and enum casting stay centralized.
- `toDto()` comes from `DataRequest`; do not hand-roll ad hoc array extraction when DTO hydration fits.
- Controllers must stay thin.
- Use `RequestActor::from($request)` whenever controller logic requires the authenticated app user.
- Prefer module-local `Queries` for reads and `Commands` for writes.
- When the same use case is exposed through both web and API transports, keep orchestration in module-local `Handlers`.
- The current `Users` module is the reference pattern for shared orchestration through `UserQueryHandler` and `UserCommandHandler`.
- Use `PageResponder` for Inertia pages and `ApiResponder` for JSON/resource responses instead of ad hoc response building.

### Mutation / Event / Realtime Pattern

- When command-side behavior fans out into audit logging, notifications, or realtime, emit one mutation context object and let listeners consume it.
- The current reference flow is:
    - `UserActionContext::fromRequest(...)`
    - `MutationMetadata::fromRequest(...)`
    - `MutationContext`
    - `UserManagementEvent`
    - listeners under `app/Modules/Users/Listeners/**`
- Domain events should remain separate from broadcast events.
- Listeners translate domain events into realtime broadcasts and notifications.
- Realtime events should extend `app/Modules/Shared/Realtime/Events/RealtimeEvent`.
- `RealtimeEvent` currently broadcasts:
    - on queue `realtime`
    - with `afterCommit = true`
- Realtime dispatch should go through `App\Modules\Shared\Realtime\Contracts\RealtimeDispatcher`.

### Canonical Reference Implementations

When adding similar behavior, inspect and follow the nearest established reference instead of inventing a new pattern:

- `Users` module:
    - query/command/handler orchestration
    - mutation context flow
    - realtime broadcasts and notifications
    - server-driven admin listing pages
- `Settings` module:
    - typed form request DTO hydration
    - page responder usage
    - schema-driven forms
- `Api/V1` module:
    - API responder usage
    - resource responses
    - shared request DTO reuse across transports
- `Shared` module:
    - module registry/discovery
    - shared realtime infrastructure
    - shared request/responders/auth helpers

### Frontend

- App entry points:
    - `resources/js/app.ts` - Client-side entry
    - `resources/js/ssr.ts` - SSR entry
    - `resources/js/create-app.ts` - Root tree and plugins shared by both entries
- Both entries call `createInertiaApp<AppPageProps>` with the same `pages` shorthand. `@inertiajs/vite` compiles it into an `import.meta.glob` resolver over `resources/js/modules/**/*.vue`, and the `transform` strips the leading `modules/` from the backend-supplied component name (`modules/users/pages/Index`).
- That `pages` object is rewritten by a static AST transform, so it must stay an inline object literal in each entry. It cannot be hoisted into a shared constant, and the duplication between the two entries is intentional.
- Passing `AppPageProps` as the type argument is what keeps `props.initialPage.props` typed; do not fall back to casting individual shared props.
- Both entries build their Vue app through `resources/js/create-app.ts`, so the root tree (`App` plus `AppToaster`) and the installed plugins are declared once and cannot drift apart. Register new plugins and root-level components there, never in a single entry.
- Pinia is created per app instance rather than at module scope, so the long-lived SSR process never shares store state between requests.
- Nothing request-scoped may be written to a module-level binding or to `globalThis`. The SSR process is long-lived and serves concurrent requests, so a per-request global is overwritten by whichever request rendered last. Current applications of this rule:
    - Pinia is per app instance (`create-app.ts`)
    - `useToast` drops toasts under SSR
    - `useApiQuery` never reads, writes, or fetches into its module-level cache or in-flight request map under SSR
    - `useAppearance` keeps only a client override at module scope, seeded from the `appearance` page prop and written solely from a browser interaction
- `app.ts` hydrates when Inertia marks the root element with `data-server-rendered` and mounts a fresh app otherwise, so `INERTIA_SSR_ENABLED` can be toggled without a hydration mismatch.
- `resources/js/ssr.ts` must stay a bare top-level `createInertiaApp(...)` statement. `@inertiajs/vite` rewrites it into the render function used by the dev SSR endpoint and the `createServer` boot used by production builds. Do not reintroduce a manual `createServer` wrapper.
- `app.ts` passes Inertia a CSP `nonce` read from the `meta[name="csp-nonce"]` tag rendered by `resources/views/app.blade.php`, so Inertia's injected style elements satisfy `SecurityHeaders`.
- The root Blade template uses `data-inertia` (not `inertia`) on head elements, per Inertia v3.
- `resources/css/app.css` is a Vite entry of its own (`vite.config.ts` `input`, first in the `@vite` array) and must never be imported from `app.ts`. SSR ships a fully rendered document, so the browser paints as soon as the HTML lands; CSS reaching it through the JS module graph would render that markup unstyled and reflow when the bundle evaluated. As a separate entry it is a render-blocking `<link rel="stylesheet">` in dev and production alike — the Vite dev server serves it as real `text/css` because a `<link>` sends `Accept: text/css`.
- The color scheme is server-rendered. `HandleAppearance` normalizes the `appearance` cookie through `App\Enums\Appearance` and shares it with the root view, which puts `class="dark"` on `<html>` directly; there is no boot script and nothing for the client to re-apply. `HandleInertiaRequests` shares the same value as a page prop so `useAppearance` seeds the appearance UI from it and hydrates without a mismatch. `useAppearance` holds only a client override (`null` until the visitor toggles), which is what keeps this module-level state out of the shared SSR process.
- Nothing that renders into markup may be nondeterministic. `Math.random()`, `Date.now()`, and `crypto.randomUUID()` produce one value in the SSR process and a different one during hydration, so Vue reports a mismatch and the server's value is the one that stays in the DOM. Use `useId()` for generated element ids and `url(#…)` references (`PlaceholderPattern`, `SidebarMenuSkeleton`); it is stable across both renders. This applies to vendored `components/ui/**` primitives too — upstream shadcn-vue does not assume SSR.
- `SecurityHeaders` allows the `http:`/`https:` scheme sources in `style-src` only when `App::isLocal()`, because the dev stylesheet is a `<link>` to the Vite dev server origin rather than to `'self'`. Production keeps the nonce-based policy, where every asset is same-origin.
- Feature pages live in `resources/js/modules/**/pages`.
- Feature forms live in `resources/js/modules/**/forms`.
- Feature-specific components live in `resources/js/modules/**/components`.
- Feature-specific contracts live in `resources/js/modules/**/contracts`.
- Feature-specific composables live in `resources/js/modules/**/composables`.
- Feature-specific helpers live in `resources/js/modules/**/helpers`.
- Shared layers:
    - `resources/js/components/**`
    - `resources/js/layouts/**`
    - `resources/js/composables/**`
    - `resources/js/config/**`
    - `resources/js/lib/**`
    - `resources/js/utils/**`

### Frontend UI Layering

- `resources/js/components/ui/**` = low-level primitive wrappers (reka-ui, icons, etc.).
- UI primitives (`resources/js/components/ui/**`) are built using shadcn-vue style components on top of Reka UI.
- Base UI component names use the `Ui*` prefix (for example `UiButton`, `UiInput`, `UiSelect`, `UiCard`, `UiDialog`). Use these existing primitives before building custom elements.
- `resources/js/components/base/**` = reusable app-level building blocks (`Base*`).
- `resources/js/modules/**` = feature-specific screens, dialogs, tables, and contracts.
- Do not place feature-specific UI in `resources/js/components/**`.
- Prefer composing `Base*` components rather than rebuilding common structures.
- Icons come from `@lucide/vue`. It exports unsuffixed names only, so use `ChevronLeft`, not `ChevronLeftIcon`. The `LucideIcon` type backs `NavItem.icon` in `resources/js/types/index.d.ts`.
- Iconify sets are also available through `unplugin-icons` using the `Icon*` component prefix.

### Frontend Automation Contracts

- Canonical auto-import config: `frontend-auto-import.config.mjs`
- The following files must stay aligned with that config:
    - `vite.config.ts`
    - `vitest.config.ts`
    - `eslint.config.js`
- Auto-imported directories currently include:
    - `resources/js/composables/**`
    - `resources/js/stores/**`
    - `resources/js/lib/**`
    - `resources/js/utils/**`
    - `resources/js/modules/**/composables/**`
    - `resources/js/modules/**/helpers/**`
- Module-local `forms/**` and `contracts/**` are not auto-imported.
- `frontend-auto-import.config.mjs` owns two contracts:
    - symbol auto-import (`autoImportDirs`, `autoImportImports`, restricted paths/patterns)
    - component auto-registration (`componentAutoImportOptions`, `inertiaComponentResolver`, `iconComponentPrefix`)
- Vue components are auto-registered from:
    - `resources/js/components`
    - `resources/js/layouts`
    - `resources/js/modules`
- `vitest.config.ts` installs the same `unplugin-vue-components` and `unplugin-icons` setup as `vite.config.ts`, so a mounted component resolves `Ui*`, `Base*`, and module components exactly as it does at runtime. Only stub children that need a live runtime dependency, such as `Link`.
- Component auto-registration only rewrites compiled SFC templates. A runtime `template` string in a spec still needs an explicit component reference.
- Auto-imported symbols used only inside a `<template>` are not typed by `vue-tsc`, because the generated `declare module 'vue'` augmentation does not merge into `@vue/runtime-core`. Derive a `computed` in `<script setup>` rather than calling an auto-imported helper directly in markup.
- Module Vue components are namespace-registered; use tags like `<UsersTable />` and `<UsersDeleteUserDialog />` instead of manual imports.
- `Link` and `Head` are resolver-provided; do not manually import them.
- The ESLint config currently enforces:
    - no direct imports of auto-imported composables/stores/libs/utils/helpers/components
    - no cross-module imports between feature modules
    - no inline `fields = [...]` form arrays in pages/components
    - no duplicated navigation arrays in pages/layouts/composables
    - no `fetch(...)` calls inside feature page files
    - no `as unknown as Record<string, unknown>`
    - no explicit any type (`@typescript-eslint/no-explicit-any`)
- Restricted imports use `@typescript-eslint/no-restricted-imports` with `allowTypeImports: true`. Auto-import only provides runtime values, so `import type { ... }` from a restricted path stays legal everywhere.
- Two scopes are deliberately exempt from the auto-import restriction:
    - files inside the auto-imported directories themselves (`autoImportSourceGlobs`), which wire up their own siblings with explicit imports instead of relying on auto-import resolving back into the directory being scanned
    - test files, which must be able to name real components and module namespaces for mounting and spying
- Feature-module source and test files still enforce the cross-module boundary rule, including type-only imports.

### Frontend Ownership Rules

- Module-specific frontend code must stay under `resources/js/modules/<module>/**`.
- Feature modules must not import other feature modules directly.
- If code is needed across modules, promote it to a shared layer instead of crossing module boundaries.
- `resources/js/types/index.d.ts` is for app-shell/shared UI types, not duplicated backend domain contracts.

### Navigation And Breadcrumb Contracts

- Shared navigation config: `resources/js/config/navigation.ts`
- Shared breadcrumb builders: `resources/js/config/breadcrumbs.ts`
- Active-state resolution lives in `resources/js/composables/useNavigation.ts`
- Dashboard CRUD navigation discovery lives in `resources/js/config/dashboard-crud-navigation.ts`
- Generated CRUD dashboard navigation contracts live at `resources/js/modules/<module>/contracts/dashboard-nav.ts`
- Do not duplicate navigation or breadcrumb arrays inline in pages/components/layouts.

## Current Route Contract

### Web Routes

- Marketing:
    - `GET /` -> `marketing.home`
- Auth guest routes under `/auth`:
    - `GET /auth/register` -> `auth.register.create`
    - `POST /auth/register` -> `auth.register.store`
        - middleware: `guest`, `throttle:auth-sensitive`
    - `GET /auth/login` -> `auth.login.create`
    - `POST /auth/login` -> `auth.login.store`
    - `GET /auth/forgot-password` -> `auth.password.request`
    - `POST /auth/forgot-password` -> `auth.password.email`
        - middleware: `guest`, `throttle:auth-sensitive`
    - `GET /auth/reset-password/{token}` -> `auth.password.reset`
    - `POST /auth/reset-password` -> `auth.password.store`
        - middleware: `guest`, `throttle:auth-sensitive`
- Authenticated auth routes under `/auth`:
    - `GET /auth/verify-email` -> `auth.verification.notice`
    - `GET /auth/verify-email/{id}/{hash}` -> `auth.verification.verify`
        - middleware: `auth`, `signed`, `throttle:6,1`
    - `POST /auth/email/verification-notification` -> `auth.verification.send`
        - middleware: `auth`, `throttle:auth-sensitive`
    - `GET /auth/confirm-password` -> `auth.password.confirm`
    - `POST /auth/confirm-password` -> `auth.password.confirm.store`
    - `POST /auth/logout` -> `auth.logout`
- App shell:
    - `GET /app/dashboard` -> `app.dashboard`
        - middleware: `auth`, `verified`
- Settings routes under `/app/settings`:
    - `GET /app/settings/profile` -> `app.settings.profile.edit`
    - `PATCH /app/settings/profile` -> `app.settings.profile.update`
    - `DELETE /app/settings/profile` -> `app.settings.profile.destroy`
    - `GET /app/settings/password` -> `app.settings.password.edit`
    - `PUT /app/settings/password` -> `app.settings.password.update`
    - `GET /app/settings/appearance` -> `app.settings.appearance`
    - `GET /app/settings` redirects to `/app/settings/profile`
    - middleware: `auth`
- Admin users routes under `/app/admin/users`:
    - `GET /app/admin/users` -> `app.admin.users.index`
    - `POST /app/admin/users` -> `app.admin.users.store`
    - `PUT /app/admin/users/{user}` -> `app.admin.users.update`
    - `PATCH /app/admin/users/{user}` -> `app.admin.users.update`
    - `DELETE /app/admin/users/{user}` -> `app.admin.users.destroy`
    - middleware: `auth`, `verified`, `can:manage-users`

### API Routes

- API broadcast auth endpoints defined in `routes/api.php`:
    - `GET|POST /api/broadcasting/auth`
    - `GET|POST /api/broadcasting/user-auth`
    - middleware: `auth:sanctum`
    - CSRF verification removed for these endpoints
- Versioned API under `/api/v1`
- Current user endpoint:
    - `GET /api/v1/me` -> `api.v1.me.show`
    - middleware: `auth:sanctum`
- Admin users API under `/api/v1/admin/users`:
    - `GET /api/v1/admin/users` -> `api.v1.admin.users.index`
    - `POST /api/v1/admin/users` -> `api.v1.admin.users.store`
    - `PUT /api/v1/admin/users/{user}` -> `api.v1.admin.users.update`
    - `DELETE /api/v1/admin/users/{user}` -> `api.v1.admin.users.destroy`
    - middleware: `auth:sanctum`, `can:manage-users`

### Gates And Policies

- `AuthServiceProvider` registers policies from `ModuleRegistry::policyMap(...)`.
- `AuthServiceProvider` requires all discovered module gate files.
- Current module gate:
    - `manage-users` in `app/Modules/Users/Routes/gates.php`
- Current module policy:
    - `app/Modules/Users/Policies/UserPolicy.php`

## Type-Safety Rules

### Backend

- Naming must stay explicit and predictable.
- Controllers should be transport-oriented and action-specific.
- Queries should expose read behavior.
- Commands should expose write behavior.
- Handlers should orchestrate shared use cases across transports when needed.
- DTOs crossing the backend/frontend boundary must remain globally unique and module-prefixed when generated CRUD naming applies.
- Use DTOs and enums instead of untyped arrays or bounded strings whenever data crosses layers.
- Any backend DTO or enum consumed by the frontend must be exported through the TypeScript transformer.
- Prefer Spatie Data classes for payload/query/page contracts.
- Annotate frontend-facing DTOs/enums with `#[TypeScript]`. Enums under `app/**` are collected regardless of the attribute.
- TypeScript generation is configured in `app/Providers/TypeScriptTransformerServiceProvider.php`. Transformer v3 has no config file; do not reintroduce `config/typescript-transformer.php`.
- That provider is the single place that owns the generated contract surface. Current settings:
    - `DataClassTransformer(nullableAsOptional: true)` so nullable DTO properties stay `foo?: T` rather than `T | null`
    - `EnumTransformer(useUnionEnums: false)` so PHP enums become native TypeScript enums
    - `FlatModuleWriter('app-data.ts')` into `resources/js/types`, keeping one flat ES module
    - `withoutManifest()` so no transformer manifest file lands in `resources/js/types`
- Route and controller type generation from the transformer stays off. Wayfinder owns that surface.
- Current shared auth user contract is `App\Modules\Shared\Data\UserViewData|null`; do not serialize the raw user model into Inertia props.
- Request DTO hydration must be the canonical transport boundary.
- Services, queries, commands, and handlers must accept DTOs or explicit typed parameters, never mixed arrays.
- Prefer module-prefixed DTO names for generated CRUD contracts:
    - `BillingIndexStoreData`
    - `BillingIndexListItemData`
    - `BillingIndexPageData`
- Use enums for constrained sort/order/event/channel values.
- Current server-table sort direction enum is `App\Modules\Shared\Enums\SortDirection`.
- Current users sort enum is `App\Modules\Users\Enums\UserSortBy`.

### Frontend

- Use `<script setup lang="ts">`.
- Keep strict TypeScript assumptions intact.
- Consume backend-generated contracts from `resources/js/types/app-data.ts`.
- Do not recreate backend-owned DTOs, enums, realtime payloads, or route payload shapes manually.
- Prefer `FormValuesFromData<...>` from `resources/js/lib/forms.ts` when form values come from backend DTOs.
- Prefer `defineFormContract(...)` and `defineFormFields(...)` for all form schemas. Both live in `resources/js/lib/forms.ts` and are auto-imported; do not import them manually. `resources/js/types/base-ui.ts` keeps the field/schema _types_ only.
- Prefer `useSchemaResourceForm(...)` over hand-wired form state in page/components when the form matches the shared resource pattern.
- Use generated route/action helpers rather than hardcoded URLs.
- Use `useApiQuery`, `useApiMutation`, and `apiRequest` for API-driven state.
- `useApiQuery` is client-only by design: it never fetches or touches its shared cache during SSR. Server-rendered page data must arrive through Inertia props.
- `useApiQuery` semantics worth knowing before changing it:
    - Concurrent consumers of the same cache key share one request, including a forced `refresh()`. Only the raw fetch is shared; `select` and `mapError` stay per caller.
    - `queryCache` stores the raw `queryFn` result, never a `select` projection, so one entry can serve consumers that project the same key differently. `getApiQueryCacheData`/`setApiQueryCacheData` operate on that raw shape.
    - Invalidations and explicit cache writes version the key and drop its in-flight entry, so a request that started earlier cannot be joined or overwrite newer/optimistic data when it settles.
    - A request for an earlier reactive key may populate that key's cache, but it cannot overwrite the composable state for the current key.
    - A projected result type requires an explicit `select`; identity queries preserve `TData` and cannot assert an unrelated result type.
    - A disabled query is never `isLoading`; disabling it supersedes observer updates from in-flight work, and `isSuccess` additionally requires resolved data.
- Wayfinder's generated route/action helpers are the only route surface. Ziggy is deliberately not a dependency: its `route()` is string-keyed rather than type-checked, and shipping its route table in every Inertia response duplicates what Wayfinder already generates at build time.
- `apiRequest` callers must validate payloads with `parseResponse` when a typed runtime contract matters.
- `apiRequest` is the canonical place for `X-Socket-ID` propagation.
- Realtime channel strings must be derived from backend-owned patterns through `resolveRealtimeChannel(...)`.
- Shared UI primitives must include baseline accessibility: visible focus states, meaningful `aria-*` labels for icon-only controls, keyboard-operable interactions, and color-contrast-safe active/focus states.
- Avoid unsafe casts like `as User`; guard nullable values explicitly.

### Backend-Driven Contract Pipeline

1. Define or update the backend enum / DTO.
2. Add `#[TypeScript]` if the frontend consumes it.
3. Use it in requests, queries, commands, handlers, controllers, resources, and realtime payloads.
4. Run generation commands.
5. Consume the generated contract from `@/types/app-data`.
6. Update tests.

## Realtime Standard

### Backend

- Reverb is the current default broadcaster in `.env.example`.
- Shared notification channel authorization is defined in `app/Modules/Shared/Routes/channels.php`.
- Users realtime channel authorization is defined in `app/Modules/Users/Routes/channels.php`.
- Root channel aggregation lives in `routes/channels.php`.
- Current backend-owned realtime enums:
    - `App\Modules\Shared\Enums\SharedRealtimeChannel`
    - `App\Modules\Users\Enums\UsersRealtimeChannel`
    - `App\Modules\Users\Enums\UsersRealtimeEvent`
    - `App\Modules\Users\Enums\UsersRealtimeAction`
- Presence member payload contract:
    - `App\Modules\Shared\Realtime/Data/PresenceMemberData`
- Broadcast notification payload contract:
    - `App\Modules\Users/Data/UserManagementNotificationData`
- Keep domain events separate from broadcast events.
- Let listeners translate domain events into realtime broadcasts and notifications.

### Frontend

- Initialize Echo only through `configureRealtime()` in `resources/js/lib/realtime/config.ts`. Both `resources/js/app.ts` and `resources/js/ssr.ts` call it.
- `configureRealtime()` is SSR-aware: under `import.meta.env.SSR` it configures the `null` broadcaster, so realtime composables resolve to inert channels instead of throwing or opening a WebSocket while the server renders. Never make a realtime composable depend on a live connection at `setup()` time.
- Shared realtime frontend helpers live in:
    - `resources/js/lib/realtime/config.ts`
    - `resources/js/lib/realtime/channels.ts`
    - `resources/js/composables/useRealtime.ts`
    - `resources/js/composables/useRealtimeConnection.ts`
- Current shared realtime composables:
    - `useRealtimeEvent`
    - `useRealtimeModel`
    - `useRealtimeNotification`
    - `useRealtimePresence`
    - `useRealtimeConnection`
- Feature-level usage helpers should live in module-local `contracts/realtime.ts`.
- The current reference implementation is `resources/js/modules/users/contracts/realtime.ts`.

### Runtime

- Queue workers must prioritize `realtime,high,default`.
- Local dev and production-style runtime must run Reverb alongside queue workers.
- Frontend realtime config currently supports:
    - session auth via `/broadcasting/auth` and `/broadcasting/user-auth`
    - bearer auth via `/api/broadcasting/auth` and `/api/broadcasting/user-auth`
- Do not hand-attach broadcast auth endpoints in feature code.

## Forms, Tables, And Page Data

- Form schemas belong in `resources/js/modules/**/forms/*-form-schema.ts`.
- Do not define inline schema arrays in pages/components.
- Current shared form contracts live in:
    - `resources/js/lib/forms.ts`
    - `resources/js/types/base-ui.ts`
    - `resources/js/components/base/forms/**`
- Server-driven listing pages must use the shared table stack:
    - `resources/js/composables/useServerDataTable.ts`
    - `resources/js/components/base/table/**`
- Initial query state must be derived via `resolveServerTableInitialQuery(...)`.
- That derivation reads the request URL from the shared `location` prop, which `HandleInertiaRequests::share()` sets from `$request->fullUrl()`. It must stay absolute and keep the query string: Inertia's own `page.url` is relative, and `$request->url()` would drop the query and silently reset a shared or bookmarked listing URL to the default sort.
- Current standard server-table query contract:
    - `page: number`
    - `perPage: number`
    - `search?: string`
    - `sortBy?: string`
    - `sortDirection?: 'asc' | 'desc'`
- Current allowed users `sortBy` values:
    - `name`
    - `email`
    - `role`
    - `created_at`

## Notifications Standard

- Global toasts are handled via `useToast` + `AppToaster`.
- Inertia flash props (`message`, `error`, `status`) are bridged to toasts through `useFlashToasts`.
- Toasts are client-only: `useToast` drops them during SSR and `AppToaster` renders none until it is mounted, which keeps the server output and the first client render identical. Do not remove either guard without moving the toast state out of module scope.
- Use inline messages only for persistent instructional content that should not be transient.

## Error And Exception Contract

- Web flows should prefer standard Laravel validation, redirects, flash messaging, and shared Inertia error handling.
- API flows should prefer `ApiResponder` plus consistent JSON/resource responses rather than ad hoc payload shapes.
- Do not introduce feature-local exception handling conventions when the shared application exception flow already covers the case.
- Throw exceptions deliberately and only when they represent a real exceptional path that callers can handle consistently.

## Security Rules

- Enforce authorization through policies, gates, and route middleware.
- Sensitive auth endpoints must use the `auth-sensitive` rate limiter.
- `AppServiceProvider` currently defines `auth-sensitive` as `5` requests per minute per IP.
- Security headers must continue to be set by `App\Http\Middleware\SecurityHeaders`.
- Current headers include:
    - CSP
    - `Referrer-Policy`
    - `X-Content-Type-Options`
    - `X-Frame-Options`
    - `Permissions-Policy`
- Non-production CSP is intentionally looser for local development.
- Audit and mutation metadata must preserve useful context while avoiding sensitive-field leakage.

## Database And Migration Rules

- Migrations must be reversible whenever reasonably possible.
- Avoid destructive schema changes without a safe transition plan.
- New searchable or sortable fields must be evaluated for indexing.
- Query changes must consider pagination, sorting, filtering, and N+1 behavior.
- Backfills or data repair logic must be explicit; do not hide data migrations inside unrelated application code.
- Model changes must preserve existing casts, auth behavior, and serialization contracts unless the task intentionally changes them.

## Generated Files

Do not hand-edit generated artifacts:

- `bootstrap/cache/modules.php`
- `resources/js/routes/**`
- `resources/js/actions/**`
- `resources/js/wayfinder/index.ts`
- `resources/js/types/app-data.ts`
- `resources/js/types/auto-imports.d.ts`
- `resources/js/types/components.d.ts`

Generated artifacts are canonical outputs of backend contracts and generator workflows.
Do not shadow them with manual duplicates.

## Generation Commands

- Route/type/helper generation:
    ```bash
    composer generate
    ```
- `composer generate` currently runs:
    - `php artisan modules:cache --no-interaction`
    - `php artisan typescript:transform`
    - `php artisan wayfinder:generate`
- Full mutating cleanup:
    ```bash
    composer generate-and-cleanup
    ```
- `composer generate-and-cleanup` currently runs:
    - route/type/helper generation
    - `vendor/bin/rector process`
    - `vendor/bin/pint --parallel`
    - `vendor/bin/phpstan analyse --ansi`
    - `npm run cleanup`
- PHP quality tools have canonical Composer entry points:
    - `composer refactor` / `composer refactor:check`
    - `composer format` / `composer format:check`
    - `composer analyse`
- Rector runs before Pint so AST rewrites always receive the final canonical formatting pass.
- `npm run cleanup` currently runs:
    - `npm run format`
    - `npm run lint`
    - `npm run typecheck`
- Non-mutating QA:
    ```bash
    composer qa:check
    ```
- `composer qa:check` runs Rector in dry-run mode, Pint in test mode, PHPStan at `max`, then frontend format, lint, and type checks. It must never rewrite source files.

## Dependency Addition Policy

- Prefer the existing Laravel, Vue, Inertia, Spatie Data, and shared project abstractions before adding new dependencies.
- New packages require strong justification and must not overlap meaningfully with tools already in the stack.
- Prefer native framework capabilities when they are already sufficient and project-consistent.
- Avoid introducing dependencies that make generated contracts, SSR, strict typing, or CI harder to maintain.

## Module Generator Contract

- Command:
    ```bash
    php artisan generate:module <ModuleName>
    ```
- Supported scaffolds:
    - `page` - Frontend-only page contracts
    - `crud` - Full backend + frontend CRUD
    - `api` - Backend API only
    - `crud-api` - Full CRUD with both web and API
- `--extend` requires the module to already exist.
- Fresh mode fails if the module already exists.
- Interactive shells prompt for scaffold/profile choices and, unless disabled, per-file generation confirmation.
- Use `--no-file-prompts` to skip per-file confirmations.
- Use `--dry-run` to inspect the plan without writing files.
- Use `--force` to overwrite existing generated files.
- Use `--base-path` only for tests or isolated generation scenarios.
- Command options contract:
    - `--route-profile=app|public|custom` (non-interactive defaults to `app`)
    - `--roles=all|admin,user` (required when scaffolding includes app CRUD web routes or protected API routes; supports `all` or comma-separated values from `App\Enums\UserRole`)
    - `--route-prefix=...` and `--route-name-prefix=...`
    - `--middleware=auth,verified`
    - `--api-route-profile=protected|public|custom` (non-interactive defaults to `protected`)
    - `--api-route-prefix=...` and `--api-route-name-prefix=...`
    - `--api-middleware=auth:sanctum`
    - `--no-api-resource`, `--no-api-test`, `--no-model`, `--no-page`

### Current Generator Behavior

- `page` scaffolds frontend-only page contracts:
    - `resources/js/modules/<module>/forms/<page-kebab>-form-schema.ts`
    - `resources/js/modules/<module>/pages/<Page>.vue`
    - `resources/js/modules/<module>/pages/__tests__/<Page>.test.ts`
- `crud` scaffolds:
    - module-local requests, DTOs, queries, commands
    - web controller
    - web routes
    - page/list DTOs
    - resource manifest
    - optional gate file depending on role scope
    - model + migration unless skipped
    - frontend CRUD page/contracts/components unless `--no-page`
    - feature test
- `api` scaffolds:
    - module-local requests, DTOs, queries, commands
    - API controller
    - API routes
    - optional `JsonResource`
    - model + migration unless skipped
    - API feature test unless skipped
- `crud-api` scaffolds both web and API layers and additionally generates module-local handlers.

### Current Generated CRUD Frontend Files

- `resources/js/modules/<module>/contracts/<page-kebab>-crud.ts`
- `resources/js/modules/<module>/contracts/dashboard-nav.ts`
- `resources/js/modules/<module>/forms/<page-kebab>-form-schema.ts`
- `resources/js/modules/<module>/components/Table.vue`
- `resources/js/modules/<module>/components/<Page>FormDialog.vue`
- `resources/js/modules/<module>/components/<Page>DeleteDialog.vue`
- `resources/js/modules/<module>/components/<Page>DetailsDialog.vue`
- `resources/js/modules/<module>/pages/<Page>.vue`
- `resources/js/modules/<module>/pages/__tests__/<Page>.test.ts`

### Current Generated CRUD Backend Files

- `app/Modules/<Module>/Http/Controllers/<Page>Controller.php`
- `app/Modules/<Module>/Http/Requests/<Page>StoreRequest.php`
- `app/Modules/<Module>/Http/Requests/<Page>UpdateRequest.php`
- `app/Modules/<Module>/Data/<Module><Page>StoreData.php`
- `app/Modules/<Module>/Data/<Module><Page>ListItemData.php`
- `app/Modules/<Module>/Data/<Module><Page>PageData.php`
- `app/Modules/<Module>/Queries/<Model>Queries.php`
- `app/Modules/<Module>/Commands/<Model>Commands.php`
- `app/Modules/<Module>/Manifests/<Page>Resource.php`
- `app/Modules/<Module>/Routes/web.php`
- `app/Modules/<Module>/Routes/gates.php` when generated app CRUD or protected API routes are role-restricted
- `tests/Feature/<Module>/<Page>PageTest.php`
- `app/Models/<Model>.php` unless skipped
- `database/migrations/*_create_<table>_table.php` unless skipped

### Current Generated API Backend Files

- `app/Modules/<Module>/Http/Controllers/<Page>ApiController.php`
- `app/Modules/<Module>/Routes/api.php`
- `app/Modules/<Module>/Http/Resources/<Page>Resource.php` unless `--no-api-resource`
- `tests/Feature/<Module>/<Page>ApiTest.php` unless `--no-api-test`

### Current Generator Route Defaults

- Web `app` profile:
    - default prefix: `app/<module-kebab>`
    - default name prefix: `app.<module-kebab>`
    - default middleware: `auth`, `verified`
- Web `app` profile with admin-only role scope:
    - default prefix: `app/admin/<module-kebab>`
    - default name prefix: `app.admin.<module-kebab>`
    - default middleware: `auth`, `verified`, `can:manage-<module-kebab>`
- Web `public` profile:
    - default prefix: `<module-kebab>`
    - default name prefix: `<module-kebab>`
    - default middleware: none
- API `protected` profile:
    - default module route prefix: `v1/admin/<module-kebab>` (Laravel applies the external `/api` prefix)
    - default name prefix: `api.v1.admin.<module-kebab>`
    - default middleware: `auth:sanctum`, plus `can:manage-<module-kebab>` for role-restricted scopes
- API `public` profile:
    - default module route prefix: `v1/<module-kebab>` (Laravel applies the external `/api` prefix)
    - default name prefix: `api.v1.<module-kebab>`
    - default middleware: none

### Resource Manifest Rules

- Generated CRUD resources own a manifest at `app/Modules/<Module>/Manifests/<Page>Resource.php`.
- The manifest is the module-local source of truth for:
    - route profile
    - route prefix
    - route name prefix
    - role scope
    - middleware
    - API defaults
    - table columns
    - mobile fields
    - form fields
    - realtime enablement
- When regenerating an existing CRUD resource, the generator should reuse manifest defaults instead of forcing the same options to be passed again.

## Performance Guardrails

- Avoid repeated filesystem discovery, reflection, or route scanning outside the existing registry/generation flow.
- Avoid N+1 queries and unnecessary model hydration on backend listing/detail endpoints.
- Prefer eager loading and focused selects when query complexity grows.
- Avoid unnecessary frontend reloads, refetches, or watchers when existing state can be updated deterministically.
- Reuse shared caches, query utilities, and realtime invalidation hooks instead of duplicating fetch logic.
- Performance optimizations must remain readable and consistent with the codebase; do not introduce obscure micro-optimizations.

## Testing Standards

- Pest 5 is the canonical backend test runner. Write backend tests in native Pest functional syntax.
- `tests/Pest.php` binds both `Feature` and `Unit` suites to `Tests\TestCase`.
- Pest's compact printer is configured centrally in `tests/Pest.php`; do not duplicate `--compact` across scripts.
- PHPUnit is configured to fail on every reported issue and to detect unexpected global-state changes or test output.
- PHPStan analyzes native Pest closures through `pestphp/pest-plugin-phpstan`; keep its extension registered in `phpstan.neon.dist`.
- PHPStan runs at `max` with `phpstan/phpstan-strict-rules` and `phpstan/phpstan-deprecation-rules`, with no baseline or ignored-error escape hatch.
- The strict rule forbidding dynamic calls to static methods is disabled because Pest/PHPUnit assertions and Laravel fluent builders intentionally expose instance syntax over methods declared static. Keep all other strict rules enabled.
- Apply `RefreshDatabase` only in files that need database isolation; do not make it global.
- Reusable backend test setup and fixture behavior belongs under `tests/Support/**`, not in duplicated file-local functions.
- Module generator backend test stubs must generate native Pest tests.
- Every behavior change must include tests for:
    - happy path
    - failure path
    - relevant edge cases
- Extend the nearest existing test suite instead of creating disconnected coverage patterns.
- Current backend test areas already include:
    - auth flows
    - settings flows
    - dashboard and marketing rendering
    - users web management
    - users realtime
    - API v1 endpoints
    - security headers
    - module registry / route / gate / channel / listener / provider discovery
    - generator behavior
- Frontend logic tests live as `resources/js/**/*.test.ts` and run under Vitest with `jsdom`.

## Test Placement Rules

- Backend transport behavior belongs in `tests/Feature/**`.
- Backend pure/domain orchestration behavior belongs in `tests/Unit/**`.
- Module-specific backend tests should stay grouped under their module namespace when practical.
- Frontend composable, contract, and utility tests should live close to the code under `resources/js/**/__tests__/**` or `resources/js/**/*.test.ts`.
- Generator and infrastructure behavior should be covered near their owning area, not mixed into unrelated feature suites.

## Docs Sync Rules

- When behavior, architecture, setup, or generation workflows change, update the relevant docs in the same task.
- Review and update these files when relevant:
    - `AGENTS.md`
    - `README.md`
    - `docs/frontend-automation.md`
    - `docs/how-to-add-module-page.md`
- Do not leave known stale instructions behind after changing the implementation.

## Quality Gate

Always run after changes:

```bash
composer generate-and-cleanup
composer test
```

For frontend behavior or composable changes, also run:

```bash
npm run test:unit
```

For non-mutating static verification, run:

```bash
composer qa:check
```

If backend route, enum, DTO, channel, provider, gate, listener, or module-registry contracts changed, `composer generate` is mandatory before considering the task complete.

`composer generate-and-cleanup` must finish without unresolved errors or warnings before a task is considered complete.

## CI Compatibility

Local changes must remain compatible with the existing CI expectations:

- Pint
- Rector dry-run
- PHPStan `max` with strict and deprecation rules
- Prettier
- ESLint
- TypeScript typecheck
- Pest / `composer test`
- Vitest for frontend logic changes

## Laravel Boost MCP Workflow

When Laravel Boost MCP tools are available to the agent:

- Use `search-docs` before Laravel/Inertia/Wayfinder/Sanctum/Tailwind ecosystem changes.
- Use `list-artisan-commands` before running Artisan commands.
- Run Artisan with `--no-interaction` when the specific command supports it.
- Use MCP equivalents (`tinker`, `database-query`, `browser-logs`) when applicable. If MCP tools fail, fallback to equivalent terminal shell commands.

## Breaking Change Policy

- Treat the following as breaking unless every consumer is updated in the same task:
    - route names or URI contracts
    - DTO or resource payload shapes
    - enum values
    - generated type/action/route outputs
    - realtime channel patterns, event names, or payload contracts
    - policy/gate ability names
- If a breaking contract change is intentional, update all affected backend code, frontend consumers, tests, generated artifacts, and docs together.

## Final Enforcement Rules

- Do not hand-wave or defer required implementation steps.
- Do not preserve stale abstractions because they already exist in a draft, comment, or doc.
- Do not add new abstractions when an existing query, command, handler, responder, composable, or base component already solves the problem.
- Do not move module-specific code into shared layers prematurely.
- Do not couple frontend modules directly to each other.
- Do not bypass generated contracts with manual copies.
- Do not ship code that is untested, partially wired, or inconsistent with the rest of the repository.
- The correct solution is the one that is accurate, complete, typed, cohesive, maintainable, and aligned with the current implementation of this codebase.
- Never lose full context of things you started working on (tasks, full logic of each task, tasks done, tasks remaining) until everything is successfully done.
- Never make assumptions; if you don't know something or have some doubts, stop and ask questions.
