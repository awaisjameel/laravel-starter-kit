# AGENTS.md

## Intent

This repository is a modular Laravel + Inertia + Vue starter kit optimized for strict type safety, security, and reusability.
The architecture is backend-contract-driven: backend DTOs/enums are the source of truth and frontend types must be derived from generated artifacts.

## Mandatory Workflow

1. Understand current implementation and affected modules before writing code.
2. Reuse existing module services, requests, DTOs, and shared UI primitives before creating new abstractions.
3. Implement focused, typed changes with no duplication.
4. Run mandatory quality checks after every change:
   - `composer generate-and-cleanup`
   - targeted PHPUnit tests (or full `php artisan test` for broad changes)
5. Update this file when architecture, contracts, workflows, or enforcement rules change.
6. Keep module integrity strict:
   - module-specific code stays inside its module on both backend and frontend.
   - only genuinely cross-cutting code belongs in shared/global layers.

## Stack Snapshot

- PHP: `^8.4`
- Laravel: `^12.53`
- Inertia Laravel: `^2`
- Sanctum: `^4`
- Wayfinder + Ziggy: typed route/actions generation
- Spatie Laravel Data + TypeScript Transformer
- Vue `^3.5`, TypeScript strict, Tailwind CSS v4

## Architecture (Canonical)

### Backend

- Entry points:
  - `routes/web.php` auto-discovers module web routes from `app/Modules/**/Routes/web.php` (canonical modules are loaded first for deterministic order).
  - `routes/api.php` auto-discovers module API routes from `app/Modules/**/Routes/api.php` (canonical modules are loaded first for deterministic order).
- Module root: `app/Modules`
  - `Marketing`
  - `Auth`
  - `Dashboard`
  - `Settings`
  - `Users`
  - `Api/V1`
  - `Shared`
- Shared domain model remains in:
  - `app/Models/User.php`
  - `app/Enums/UserRole.php`
- Shared middleware:
  - `HandleAppearance`
  - `HandleInertiaRequests`
  - `SecurityHeaders`

### Backend Module Ownership Contract (Strict)

- Module-specific backend code must live under its module namespace/path (`app/Modules/<Module>/**`):
  - Controllers
  - Requests
  - Data DTOs
  - Services
  - Policies/Gates
  - Resources/Transformers
  - Events/Listeners
- Keep `app/Models/**` and `app/Enums/**` for truly shared domain primitives only.
- If a class is used by multiple modules, move it to `app/Modules/Shared/**` (or existing global shared location) instead of duplicating.
- Do not place module-only classes under shared/global directories.

### Frontend

- Inertia pages live in `resources/js/modules/**/pages`.
- Feature form schemas live in `resources/js/modules/**/forms`.
- Frontend config contracts live in `resources/js/config/**`.
- Shared app/layout primitives remain in:
  - `resources/js/components/**`
  - `resources/js/layouts/**`
- UI layering contract:
  - `resources/js/components/ui/**` = low-level primitive wrappers only.
  - `resources/js/components/base/**` = reusable app-level building blocks (`Base*`).
  - Feature-specific screens should compose `Base*` components instead of hand-rolling repeated structures.
- App entry points:
  - `resources/js/app.ts`
  - `resources/js/ssr.ts`
- Auto-import contract:
  - Canonical source is `frontend-auto-import.config.mjs`.
  - `vite.config.ts`, `vitest.config.ts`, and `eslint.config.js` must consume this shared config; do not duplicate symbol lists.
  - Module `forms/**`, `composables/**`, `contracts/**`, and `helpers/**` exports are auto-imported; do not manually import these paths in frontend files.
  - Module Vue components are auto-registered from `resources/js/modules/**` using namespace-style names (`<Module><Component>`), e.g. `UsersTable`, `SettingsDeleteUser`; do not manually import module components.
- Navigation contract:
  - Centralized in `resources/js/config/navigation.ts`.
  - `useNavigation` and settings layout must consume shared navigation builders, not duplicate route/label arrays.
  - Navigation items may declare `activeMatch` (`exact` or `prefix`) to control active-state behavior.
- Breadcrumb contract:
  - Centralized in `resources/js/config/breadcrumbs.ts`.
  - Feature pages should consume shared breadcrumb builders, not duplicate breadcrumb arrays inline.

### Frontend Module Ownership Contract (Strict)

- Module-specific frontend code must live under `resources/js/modules/<module>/**`:
  - `pages/**`
  - `components/**`
  - `forms/**`
  - module-local composables/contracts/helpers
- Do not keep feature-specific components in `resources/js/components/**`.
- `resources/js/components/ui/**` is primitive-only.
- `resources/js/components/base/**` is reusable app-level building blocks only.
- `resources/js/config/**`, `resources/js/layouts/**`, and `resources/js/composables/**` are shared layers; do not move module-specific logic there.
- Frontend module files under `resources/js/modules/**` must not import other feature modules directly; promote shared code instead.

### Shared Placement Rules

- Promote to shared only when at least one condition is true:
  - reused by 1+ modules
  - forms a stable cross-cutting contract
  - belongs to app shell/infrastructure concerns (layout, navigation, global composables, base UI, transport)
- If reuse is uncertain, keep code inside the owning module first and extract later when duplication appears.

## Routing Contract

### Web

- Marketing:
  - `GET /` => `marketing.home`
- Auth:
  - `/auth/*` => `auth.*`
- App shell:
  - `GET /app/dashboard` => `app.dashboard`
- Settings:
  - `/app/settings/*` => `app.settings.*`
- Admin users:
  - `/app/admin/users/*` => `app.admin.users.*`

### API

- Versioned API under `/api/v1/*`
- Authenticated current user:
  - `GET /api/v1/me` => `api.v1.me.show`
- Admin users API:
  - `/api/v1/admin/users/*` => `api.v1.admin.users.*`

## Type-Safety Rules

### Backend

- Use `declare(strict_types=1);` in every PHP file.
- Form Requests must expose typed `toDto()` methods where business input is consumed.
- Services must accept DTOs or explicit typed parameters, never untyped arrays.
- Inertia shared auth user must be a typed DTO (`UserViewData|null`), not raw model serialization.
- Any backend DTO/enum that crosses the backend/frontend boundary must be exported via TypeScript generation:
  - prefer Spatie Data classes (`extends Data`) for payload/query/page contracts.
  - annotate exported contracts with `#[TypeScript]`.
- Replace bounded string query values with enums in backend contracts (e.g., sort fields/directions), then consume generated enums in frontend.

### Frontend

- Use `<script setup lang="ts">`.
- Keep strict TS (`noImplicitAny`, `strictNullChecks`, `exactOptionalPropertyTypes`).
- Avoid unsafe casts like `as User`; guard nullable values explicitly.
- Frontend must consume backend-generated contracts from `resources/js/types/app-data.ts` for domain entities/DTOs/enums.
- Do not redefine backend-owned entity/DTO/enum types in manual frontend files.
- `resources/js/types/index.d.ts` should contain app-shell/shared UI types only, not duplicated backend domain contracts.
- Inertia page props should prefer generated backend page DTO contracts when available.
- Prefer named routes and generated helpers over hardcoded URIs.
- New forms should use schema-driven rendering via shared form contracts/components in `resources/js/components/base/forms/**`.
- Feature forms must define typed form contracts via `defineFormContract` + `defineFormFields` in module schema files (`resources/js/modules/**/forms/*-form-schema.ts`) and reuse them in pages/components.
- Feature pages should consume schema contracts with `useSchemaResourceForm` instead of duplicating `initialValues` and field wiring inline.
- Server-driven listing pages should use shared server table composables/components in `resources/js/components/base/table/**`.
- Server-driven listing pages must derive initial query state via `resolveServerTableInitialQuery` from `useServerDataTable`.
- API-driven state must use shared query contracts (`useApiQuery`, `useApiMutation`, `apiRequest`) with typed cache keys, retry policy, mapped errors, and optional optimistic updates.
- Do not call `fetch` directly inside feature page components; route data access through shared query/API composables.
- Use Wayfinder action helpers for submits/navigation in reusable composables and feature pages.
- Avoid `as unknown as Record<string, unknown>`; `BaseFormsBaseFormRenderer` accepts form objects directly.
- Do not define inline form schema arrays (`const fields = [...]`) in pages/components; keep form schemas in `resources/js/modules/**/forms`.
- Do not duplicate navigation arrays in pages/layouts/composables; centralize navigation definitions in `resources/js/config/navigation.ts`.
- Feature module files under `resources/js/modules/**` must not import other feature modules directly; move shared code to shared/base/config layers.
- Shared UI primitives must include baseline accessibility: visible focus states, meaningful `aria-*` labels for icon-only controls, keyboard-operable interactions, and color-contrast-safe active/focus states.

### Backend-Driven Type Pipeline (Non-Negotiable)

1. Define/adjust backend enum or Spatie Data DTO.
2. Annotate with `#[TypeScript]` if the contract is used on frontend.
3. Use the DTO/enum in Request `toDto()`, Services, and Controllers/Resources.
4. Run generation commands.
5. Consume generated types in frontend (`@/types/app-data`) instead of hand-written duplicates.

## Server Table Query Contract

Standard query contract for server-driven data tables:

- `page: number`
- `perPage: number`
- `search?: string`
- `sortBy?: string`
- `sortDirection?: 'asc' | 'desc'`

For users listing, allowed `sortBy` values are:

- `name`
- `email`
- `role`
- `created_at`

## Notifications Standard

- Global toasts are handled via `useToast` + `AppToaster`.
- Inertia flash props (`message`, `error`, `status`) are bridged to toasts through `useFlashToasts`.
- Use inline messages only for persistent instructional content that should not be transient.

## Security Rules

- Enforce authorization with policies/gates and middleware.
- Sensitive auth endpoints must be throttled (`auth-sensitive` limiter).
- Security headers must include CSP and nonce-based script controls.
- Audit payloads must redact sensitive fields.

## Generated Files

Do not hand-edit generated artifacts:

- `resources/js/routes/**`
- `resources/js/actions/**`
- `resources/js/wayfinder/index.ts`
- `resources/js/types/app-data.ts`
- `resources/js/types/auto-imports.d.ts`
- `resources/js/types/components.d.ts`

Generated artifacts are the canonical frontend contract output of backend types. Do not shadow them with manual duplicates.

After route/DTO/enum changes run:

1. `composer generate`
2. (if needed) `php artisan wayfinder:generate --no-interaction`

## Testing Standards

Every behavior change must include tests for:

- happy path
- failure path (validation/authorization)
- relevant edge cases

Minimum expectations:

- Route changes: feature tests for accessibility + auth + authorization + validation.
- Service changes: unit tests for public methods and edge cases.
- Bug fixes: regression tests proving bug and fix.
- Frontend composable/contract changes: add or update `vitest` coverage in `resources/js/**/__tests__/**`.

## Reusability and Duplication Rules

- No duplicated business logic across modules.
- No duplicated large UI structures; extract shared components when repeated.
- No dead code, commented-out code, placeholder TODOs, or unused scaffolding.
- Never lose full context of things you started working (tasks, full logic of each task, tasks done, tasks remaing) untile everything successfully done.
- Never make assumptions, if you don't know something or have some doubts ask questions.

## Developer Automation

- Use `php artisan generate:module <ModuleName> --scaffold=crud --page=<PageName>` to scaffold a typed module shell (backend + frontend + feature test), including:
  - module-local controller/request/data/routes under `app/Modules/<Module>/**`
  - Eloquent model in default location `app/Models/<Model>.php`
  - migration in default location `database/migrations/*_create_<table>_table.php`
  - frontend CRUD module assets under `resources/js/modules/<module>/**` (table, create/update dialog, delete dialog, details dialog, page, form schema, frontend test)
- Use `php artisan generate:module <ModuleName> --extend --scaffold=page --page=<PageName>` to scaffold page-level frontend contracts for an existing module.
- Command options contract:
  - `--scaffold=page|crud|api|crud-api` (interactive prompt when omitted; defaults to `crud` for fresh mode and `page` for extend mode)
  - `--route-profile=app|public|custom` (interactive prompt when omitted in interactive shells; non-interactive defaults to `app`)
  - `--roles=all|admin,user` (required when scaffolding includes app CRUD web routes; supports `all` or comma-separated values from `App\Enums\UserRole`)
  - `--route-prefix=...`
  - `--route-name-prefix=...`
  - `--middleware=auth,verified`
  - `--api-route-profile=protected|public|custom` (interactive prompt when omitted in interactive shells; non-interactive defaults to `protected`)
  - `--api-route-prefix=...`
  - `--api-route-name-prefix=...`
  - `--api-middleware=auth:sanctum`
  - `--no-api-resource`
  - `--no-api-test`
  - `--no-model`
  - `--no-page`
  - Interactive shells prompt per generated file by default; use `--no-file-prompts` to generate all planned files without per-file confirmations.
  - `--no-file-prompts`
  - `--force`
  - `--dry-run`
  - `--base-path=...` (testing only)
- App CRUD scaffolds (`--scaffold=crud|crud-api` with `--route-profile=app`) must define role scope via `--roles`:
  - `all` keeps default `auth` + `verified` middleware and does not generate module gate files.
  - specific roles generate module-local gate file at `app/Modules/<Module>/Routes/gates.php` and append `can:manage-<module-kebab>` middleware.
- CRUD page scaffolds with app route profile generate module-local dashboard navigation contract:
  - `resources/js/modules/<module>/contracts/dashboard-nav.ts`
  - shared dashboard navigation discovery consumes these contracts from `resources/js/config/dashboard-crud-navigation.ts`.

## Quality Gate (Non-Negotiable)

Always run after changes:

```bash
composer generate-and-cleanup
php artisan test
```

For frontend behavior/composable changes, also run:

```bash
npm run test:unit
```

For non-mutating static checks, run:

```bash
composer qa:check
```

`composer qa:check` does not regenerate route/type artifacts; run `composer generate` when backend route/DTO/enum contracts change.

## CI Compatibility

Local changes must remain compatible with existing CI checks:

- Pint / PHPStan / frontend format / frontend lint / TS typecheck / PHPUnit.
- Frontend unit tests (`vitest`) for frontend logic changes.
