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
- Prefer the simplest design that is correct, extensible, testable, and aligned with existing project patterns.
- Maintain excellent DI: prefer constructor injection, readonly dependencies, explicit typed inputs, and thin transport layers.
- Maintain excellent DX: preserve predictable file placement, generated contract flows, route/action helpers, and shared abstractions instead of ad hoc patterns.
- Maintain excellent performance: avoid duplicate scans, redundant queries, unnecessary rerenders, unnecessary network calls, and unnecessary abstractions.
- Remove dead code, duplicate logic, stale comments, unused scaffolding, and compatibility shims when they are no longer justified.
- Never leave a feature half-finished. If a change requires backend, frontend, generated artifacts, tests, docs, or cleanup, complete all of them in the same task.
- Match the existing architecture precisely. If the architecture must change, update this file in the same change.

## Mandatory Workflow

1. Inspect the existing implementation, affected modules, related tests, and generated-contract flow before making changes.
2. Reuse existing module services, requests, DTOs, queries, commands, handlers, responders, composables, and base UI primitives before introducing anything new.
3. Keep changes focused, typed, and module-local unless the concern is genuinely cross-cutting.
4. Regenerate artifacts whenever backend-owned contracts, routes, channels, providers, listeners, or enums change.
5. Run the required quality gates after every change.
6. Update `AGENTS.md` whenever architecture, workflows, or enforcement rules change.
7. Do not stop at analysis. Deliver the finished implementation, verification, and cleanup unless the user explicitly redirects you.

## Agent Workflow Protocol (MANDATORY)

Before writing ANY code, agents MUST follow this protocol:

1. Understanding Phase (REQUIRED)
    - Read First: Never assume. Read all related files, understand the full context.
    - Trace Dependencies: Map all files, services, events, and components that will be affected.
2. Planning Phase (REQUIRED)
    - Design First: Outline the solution architecture before coding.
    - Impact Analysis: List all files that need changes (backend, frontend, tests, docs).
3. Implementation Phase
    - One Change at a Time: Make focused, atomic changes.
    - Follow Patterns: Match existing conventions exactly.
4. Verification Phase
    - Run Quality Gate: `composer generate-and-cleanup` after every change.
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
- Laravel: `^12.50`
- Inertia Laravel: `^2.0.21`
- Reverb: `^1.8`
- Sanctum: `^4.3.1`
- Wayfinder: `^0.1.14`
- Ziggy: `^2.6.1`
- Spatie Laravel Data: `^4.20`
- Spatie TypeScript Transformer: `^2.6`
- Vue: `^3.5.29`
- TypeScript: `^5.9.3`
- Vite: `^7.3.1`
- Tailwind CSS: `^4.2.1`
- Node: `>=24.1.0`
- npm: `>=11.2.1`
- Package manager: `npm` ONLY. This is enforced by `ensure-node-env.js`. Do not use yarn, pnpm, or bun.

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
- `pm2.config.cjs` currently manages production-style queue workers, Reverb, and the scheduler.

## Canonical Architecture

### Backend

- Application bootstrapping is defined in `bootstrap/app.php`.
- `bootstrap/providers.php` only lists:
    - `App\Providers\AppServiceProvider`
    - `App\Providers\AuthServiceProvider`
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
    - `resources/js/ssr.ts` - SSR entry (do not initialize Echo here)
- Inertia page resolution currently loads Vue files from `resources/js/modules/**/*.vue`.
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
- Vue components are auto-registered from:
    - `resources/js/components`
    - `resources/js/layouts`
    - `resources/js/modules`
- Module Vue components are namespace-registered; use tags like `<UsersTable />` and `<UsersDeleteUserDialog />` instead of manual imports.
- `Link` and `Head` are resolver-provided; do not manually import them.
- The ESLint config currently enforces:
    - no direct imports of auto-imported composables/libs/helpers/components
    - no cross-module imports between feature modules
    - no inline `fields = [...]` form arrays in pages/components
    - no duplicated navigation arrays in pages/layouts/composables
    - no `fetch(...)` calls inside feature page files
    - no `as unknown as Record<string, unknown>`
    - no explicit any type (`@typescript-eslint/no-explicit-any`)

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
- Annotate frontend-facing DTOs/enums with `#[TypeScript]`.
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
- Prefer `defineFormContract(...)` and `defineFormFields(...)` for all form schemas.
- Prefer `useSchemaResourceForm(...)` over hand-wired form state in page/components when the form matches the shared resource pattern.
- Use generated route/action helpers rather than hardcoded URLs.
- Use `useApiQuery`, `useApiMutation`, and `apiRequest` for API-driven state.
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

- Initialize Echo only through `configureRealtime()` in `resources/js/lib/realtime/config.ts` which is called from `resources/js/app.ts`.
- Do not initialize Echo in `resources/js/ssr.ts`.
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
    - `vendor/bin/pint --parallel`
    - `vendor/bin/rector process`
    - `vendor/bin/phpstan analyse --ansi`
    - `npm run cleanup`
- `npm run cleanup` currently runs:
    - `npm run format`
    - `npm run lint`
    - `npm run typecheck`
- Non-mutating QA:
    ```bash
    composer qa:check
    ```

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
    - `--roles=all|admin,user` (required when scaffolding includes app CRUD web routes; supports `all` or comma-separated values from `App\Enums\UserRole`)
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
- `app/Modules/<Module>/Routes/gates.php` when app CRUD routes are role-restricted
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
    - default prefix: `api/v1/admin/<module-kebab>`
    - default name prefix: `api.v1.admin.<module-kebab>`
    - default middleware: `auth:sanctum`
- API `public` profile:
    - default prefix: `api/v1/<module-kebab>`
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
php artisan test
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

## CI Compatibility

Local changes must remain compatible with the existing CI expectations:

- Pint
- Rector
- PHPStan
- Prettier
- ESLint
- TypeScript typecheck
- PHPUnit / `php artisan test`
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
