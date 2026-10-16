# Backend Architecture

Read before changing application bootstrapping, modules, module discovery, ownership boundaries, requests, controllers, queries, commands, handlers, DTO/enum naming, errors, models, or migrations.

## Bootstrapping

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
- Redirect, `verified`, and stateful API configuration in the same file is described in [authentication wiring](routes-and-security.md#authentication-wiring).
- Events are registered from `app/Listeners` and module-discovered listener directories via `ModuleRegistry::listenerDirectories(...)`.

## Modules

- Module root: `app/Modules`
- Current modules:
    - `Marketing` - Public marketing pages
    - `Auth` - Authentication (login, register, password reset, email verification)
    - `Dashboard` - Authenticated app dashboard
    - `Settings` - User settings (profile, password, appearance)
    - `Users` - Admin user management with realtime
    - `Api/V1` - API v1 endpoints
    - `Shared` - Shared infrastructure and utilities
- Shared domain primitives:
    - `app/Models/User.php`
    - `app/Enums/UserRole.php`
    - `app/Enums/Appearance.php`

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
- `routes/web.php`, `routes/api.php`, and `routes/channels.php` only require the files the registry discovers, so a module's `Routes/*.php` files are picked up without editing them.
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
- Do not introduce filesystem discovery, reflection, or route, gate, channel, listener, or provider scanning outside the registry and generation flow unless the architecture is intentionally changed and this reference is updated.

## Ownership Rules

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
- If reuse spans multiple modules, move the code to `app/Modules/Shared/**` instead of duplicating it. Do not place module-only code under global shared locations.
- Backend event listeners under `app/Modules/**/Listeners/**` are auto-discovered through Laravel event discovery. Do not manually register module listener classes in `AppServiceProvider`.

## Shared Infrastructure

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
- Shared realtime infrastructure: `app/Modules/Shared/Realtime/**` (see [realtime](realtime.md))
- Current auto-discovered shared module provider:
    - `app/Modules/Shared/Providers/ModuleServiceProvider.php`

## Requests, Controllers, And Handlers

- Console commands use Laravel's typed `input()->string(...)->toString()` and `input()->boolean(...)` readers for signature-defined strings and flags.
- Prefer extending `DataFormRequest` for validated form payloads.
- Prefer extending `DataQueryRequest` for query payloads so trimming, defaults, pagination, and enum casting stay centralized.
- `toDto()` comes from `DataRequest`; do not hand-roll ad hoc array extraction when DTO hydration fits.
- Controllers stay thin, transport-oriented, and action-specific.
- Use `RequestActor::from($request)` whenever controller logic requires the authenticated app user.
- Prefer module-local `Queries` for reads and `Commands` for writes.
- When the same use case is exposed through both web and API transports, keep orchestration in module-local `Handlers`.
- Use `PageResponder` for Inertia pages (for example `PageResponder::render('modules/dashboard/pages/Index')`) and `ApiResponder` for JSON/resource responses instead of ad hoc response building.

## DTOs, Enums, And Naming

- Naming must stay explicit and predictable.
- Use DTOs and enums instead of untyped arrays or bounded strings whenever data crosses layers.
- Prefer Spatie Data classes for payload/query/page contracts.
- DTOs crossing the backend/frontend boundary must remain globally unique and module-prefixed when generated CRUD naming applies:
    - `BillingIndexStoreData`
    - `BillingIndexListItemData`
    - `BillingIndexPageData`
- Use enums for constrained sort/order/event/channel values.
- Current server-table sort direction enum is `App\Modules\Shared\Enums\SortDirection`.
- Current users sort enum is `App\Modules\Users\Enums\UserSortBy`.
- Model date annotations use `CarbonInterface` to cover mutable and immutable casts. DTO boundaries normalize non-null dates with `CarbonImmutable::instance`; never discard an immutable date with an `instanceof Carbon` check. Essentials enables immutable dates by default.
- Exporting DTOs and enums to the frontend is covered in [contracts](contracts.md#typescript-generation).

## Errors And Exceptions

- Web flows should prefer standard Laravel validation, redirects, flash messaging, and shared Inertia error handling.
- API flows should prefer `ApiResponder` plus consistent JSON/resource responses rather than ad hoc payload shapes.
- Do not introduce feature-local exception handling conventions when the shared application exception flow already covers the case.
- Throw exceptions deliberately and only when they represent a real exceptional path that callers can handle consistently.

## Database And Queries

- Migrations must be reversible whenever reasonably possible.
- Avoid destructive schema changes without a safe transition plan.
- New searchable or sortable fields must be evaluated for indexing.
- Query changes must consider pagination, sorting, filtering, and N+1 behavior. Paginated ordering ends with a unique tie breaker so pages stay stable.
- Avoid unnecessary model hydration on listing/detail endpoints; prefer eager loading and focused selects when query complexity grows.
- Backfills or data repair logic must be explicit; do not hide data migrations inside unrelated application code.
- Model changes must preserve existing casts, auth behavior, and serialization contracts unless the task intentionally changes them.

## Reference Implementations

When adding similar behavior, inspect and follow the nearest established reference instead of inventing a new pattern:

- `Users` module:
    - query/command/handler orchestration (`UserQueryHandler`, `UserCommandHandler`)
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
