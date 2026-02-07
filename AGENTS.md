# AGENTS.md

## Intent
This file defines mandatory rules for coding agents working in this repository.
The goal is clean, consistent, end-to-end type-safe, secure, and performant code.

## Stack Snapshot (Current Project)
- Backend: PHP `^8.4`, Laravel `^12.50`, Inertia Laravel `^2.0`, Sanctum `^4.3`, Wayfinder `^0.1`, Ziggy `^2.6`
- Typed backend contracts: Spatie Laravel Data `^4.19`, Spatie TypeScript Transformer `^2.5`
- Frontend: Vue `^3.5`, Inertia Vue `^2.3`, Tailwind CSS `^4.1`, Pinia `^3`
- UI primitives: shadcn-vue style components on Reka UI
- Tooling: Vite `^7`, TypeScript `^5.9` (strict), ESLint `^9`, Prettier `^3`, Pint `^1`, Rector `^2`, PHPUnit `^12`
- Node requirements: Node `>=24.1.0`, npm `>=11.2.1`, npm only (enforced by `ensure-node-env.js`)

## Non-Negotiable Rules
- Before making any code changes, fully understand all requirements and the current implementation across related files, flows, and dependencies.
- Write or modify code only after that understanding is clear, then implement in the most efficient, maintainable, and logically structured way.
- Match existing conventions before introducing new patterns.
- Reuse existing components, composables, DTOs, enums, and helpers before creating new ones.
- Do not change dependencies, core folder structure, or app architecture without explicit approval.
- Do not add new documentation files unless explicitly requested.
- No dead code, commented-out code, placeholder TODOs, or magic strings where typed/shared constants exist.
- For behavior changes, update or add tests that cover happy path, failure path, and relevant edge cases.
- Every new or changed implementation must follow the same project patterns and best practices: clean, reusable, type-safe code with the most logical structure.

## Documentation Accountability Policy
- Any new entity, feature, service, integration, or major update to existing behavior must be documented in `AGENTS.md`.
- `AGENTS.md` updates must be made in the same change set as the code change.
- Documentation must be specific enough for accountability and long-term maintenance.
- At minimum, document: purpose, impacted routes/entry points, data contracts (DTO/enums/types), validation/authorization rules, background processing, and test expectations.
- A change is not complete until `AGENTS.md` is updated when applicable.

## Architecture Map

### Backend
- Application bootstrap and middleware registration: `bootstrap/app.php`
- Providers: `bootstrap/providers.php`
- Route files:
  - `routes/web.php`
  - `routes/auth.php`
  - `routes/settings.php`
  - `routes/api.php`
  - `routes/console.php`
- Domain model:
  - `app/Models/User.php` with enum cast (`UserRole`) and hashed password cast
  - `app/Enums/UserRole.php` (`Admin`, `User`)
  - `app/Data/UserData.php` as DTO exported to TypeScript
- Authorization:
  - Gate `manage-users` in `app/Providers/AuthServiceProvider.php`
- Middleware:
  - `HandleAppearance` shares appearance from cookie
  - `HandleInertiaRequests` shares `name`, `quote`, `auth.user`, `ziggy`, `sidebarOpen`
  - Cookie encryption excludes `appearance` and `sidebar_state`
- Validation:
  - Form Requests used for user CRUD and login
  - Some auth/settings controllers still use inline `$request->validate()` (legacy starter defaults)
- Persistence:
  - Users, password reset tokens, sessions, personal access tokens
  - Cache, job, batch, failed job tables
  - Default seeded users controlled by `APP_SEED_USERS`

### Frontend
- Entry points:
  - `resources/js/app.ts` (CSR)
  - `resources/js/ssr.ts` (SSR)
- Inertia pages: `resources/js/pages` (lowercase directory is intentional)
- Layouts:
  - `AppLayout` (header shell)
  - `AppFunnelLayout` (sidebar shell)
  - `AuthLayout` -> `AuthSimpleLayout`
  - `settings/Layout.vue`
- Navigation and shell components:
  - `AppHeader`, `AppSidebar`, `AppShell`, `AppContent`, `NavMain`, `NavUser`, `UserMenuContent`
- Appearance/theming:
  - `useAppearance.ts` stores preference in `localStorage` and `appearance` cookie
  - Dark mode is class-based (`.dark`), initialized early in `resources/views/app.blade.php`
- UI component system:
  - App components: `resources/js/components`
  - UI primitives: `resources/js/components/ui`
  - Auto-registration enabled with namespaces (`directoryAsNamespace: true`)
- Type declarations:
  - Shared page props and app types in `resources/js/types`

## Existing Features (What Agents Must Know)
- Auth flows: login, logout, forgot/reset password, confirm password, email verification.
- Settings flows: profile update, password update, appearance toggle, account deletion.
- Dashboard page behind `auth` + `verified` middleware.
- User management (`/users`) for admins only:
  - Server-side pagination
  - Create, update, delete via dialogs
  - Role managed via `UserRole` enum
- API endpoint `/api/user` protected by Sanctum.

## Generated Files and Codegen
Treat these as generated artifacts and do not hand-edit unless explicitly requested:
- `resources/js/routes/**` (Wayfinder named route helpers)
- `resources/js/actions/**` (Wayfinder controller action helpers)
- `resources/js/wayfinder/index.ts`
- `resources/js/types/app-data.ts` (Spatie transformer output)
- `resources/js/types/auto-imports.d.ts`
- `resources/js/types/components.d.ts`

After schema/DTO/enum/route changes:
1. `composer generate`
2. If needed, run `php artisan wayfinder:generate --no-interaction`

## Implementation Standards

### PHP / Laravel
- All PHP files must use `declare(strict_types=1);`.
- Use explicit parameter and return types on all methods/functions.
- Prefer `final class` for concrete classes unless extension is required.
- Use Form Request classes for new validation logic.
- Use Eloquent (`Model::query()`) and relationships before dropping to query builder.
- Prevent N+1 queries with eager loading.
- Use named routes and route helpers, not hard-coded URLs.
- Keep authorization on server side (gates/policies/middleware), not only in Vue.
- Never use `env()` outside config files.

### Vue / TypeScript / JavaScript
- Default to `<script setup lang="ts">` for new Vue components.
- Prefer TypeScript files over JavaScript for new frontend logic.
- Keep strict typings end-to-end from backend DTO/enum -> frontend props/forms.
- Use Inertia primitives (`useForm`, `router`, `<Link>`) for navigation and form submission.
- Prefer Wayfinder route/action helpers over hard-coded endpoints.
- Keep pages in `resources/js/pages` and map server renders exactly.
- Reuse `resources/js/components/ui` primitives before adding new custom base components.
- All new UI must support both light and dark themes.

### Styling and UX
- Tailwind v4 only (`@import 'tailwindcss';` already configured).
- Reuse existing tokens and utilities in `resources/css/app.css`.
- Keep class lists intentional and avoid redundant utility noise.
- Use `gap-*` for spacing in lists/grouped layouts.

### Performance
- Paginate large datasets (follow `/users` pattern).
- Avoid unnecessary client state duplication of server props.
- Avoid unnecessary watchers/computeds when derived values are static.
- Keep payloads minimal and typed.

## Validation and Quality Gate
Before finalizing code changes:
1. `composer generate-and-cleanup`
2. `npm run cleanup`
3. `npm run typecheck`
4. Run targeted PHPUnit tests for changed behavior

If the change affects many areas, run full tests:
- `php artisan test --no-interaction`

## Laravel Boost MCP Workflow
When Laravel Boost MCP tools are available:
- Use `search-docs` before Laravel/Inertia/Wayfinder/Sanctum/Tailwind ecosystem changes.
- Use `list-artisan-commands` before running Artisan commands.
- Always run Artisan with `--no-interaction`.
- Use MCP equivalents (`tinker`, `database-query`, `browser-logs`) when applicable.
If MCP tools fail, use equivalent shell commands.

## Known Caveats (Current Starter Kit State)
- Registration controller and page exist, but registration routes are commented out in `routes/auth.php`.
- `Route::resource('users', ...)` includes `show` and `edit`, but `UserController` does not implement these methods.
  - Do not call these routes until controller methods and route definitions are aligned.
- `resources/stores/useHttp.js` exists outside `resources/js` and is not part of the main typed frontend pattern.
  - Prefer Inertia + Wayfinder unless you are explicitly implementing API-client logic.
- Marketing copy may mention tools not installed (for example PHPStan). Validate against `composer.json` before assuming tooling exists.

## CI Expectations
GitHub Actions currently run:
- Lint workflow: Pint, frontend format, frontend lint
- Test workflow: build assets and run PHPUnit
Agents should keep local changes compatible with those checks.
