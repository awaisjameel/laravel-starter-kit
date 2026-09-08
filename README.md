# Laravel Modular Inertia Starter Kit

A Laravel 13 + Inertia 3 + Vue 3 starter kit with strict typed contracts from backend DTOs to frontend TypeScript.

## Requirements

- PHP 8.4+
- Node 24.15+ and npm 11.2.1+
- Composer 2+

Published Sail Docker contexts are limited to PHP 8.4 and 8.5, matching the Composer runtime constraint.

## Stack

- Laravel 13, Inertia 3 (`inertiajs/inertia-laravel`, `@inertiajs/vue3`, `@inertiajs/vite`), Reverb 1.11, Sanctum 4
- Spatie Laravel Data 4 + TypeScript Transformer 3, Wayfinder
- Vue 3.5, TypeScript 7 checker via TypeScript Native Bridge, Vite 8 (Rolldown), Tailwind CSS 4, Pinia 4, Reka UI, Iconify through `unplugin-icons`
- Pest 5 (PHPUnit 13 engine), Vitest 5, Pint, PHPStan/Larastan at level 9 with strict/deprecation rules, Rector, ESLint 10, Prettier 3

TypeScript uses the 7.0.2 native checker through the exactly pinned `typescript-native-bridge` npm alias. Its classic API adapter keeps Vue, ESLint, and Prettier compatible. This is a third-party compiler bridge, not Microsoft's stock TypeScript package. See [frontend tooling and editor setup](docs/frontend-automation.md#typescript-compiler-and-editor) for platform requirements and upgrade guidance.

`composer.lock` and `package-lock.json` are committed application contracts. Use Composer and npm install commands that honor them; npm is the only supported JavaScript package manager.

## Quick Start

```bash
cp .env.example .env
composer install
npm ci
php artisan key:generate
php artisan migrate
composer dev
```

Realtime dev dependencies are included in `composer dev`; this starts Laravel Reverb alongside the web server, queue worker, logs, and Vite.

`composer dev` also gives you server-side rendering: `@inertiajs/vite` renders pages through the Vite dev server, so no separate SSR process is needed while developing. Use `composer dev:ssr` to exercise the production SSR path (built assets + `php artisan inertia:start-ssr`), and set `INERTIA_SSR_ENABLED=false` to turn SSR off.

## Core Commands

- Generate routes/types:
    - `composer generate`
- Full quality gate (mandatory after edits):
    - `composer generate-and-cleanup`
    - `composer test`
    - `npm run test:unit`
- Non-mutating QA check:
    - `composer qa:check`
- Single-stack pipelines (useful when a change only touches one side):
    - `composer cleanup:php` / `composer qa:php`
    - `npm run cleanup`
- Focused PHP tooling:
    - `composer refactor` / `composer refactor:check`
    - `composer format` / `composer format:check`
    - `composer analyse`
- Generated artifact sync check:
    - `composer generate`
    - `npm run build:ssr`

`composer run-script --list` prints a description for every script.

### Command Performance

Repeated runs are meant to be cheap, so the aggregate scripts are built for the edit-check loop:

- `composer cleanup` and `composer qa:check` fan their PHP and frontend pipelines out over `concurrently` and group each tool's output, so the slowest tool sets the wall time instead of the sum of all of them.
- `composer test` runs Pest in parallel by default. Use `composer test:serial` when a single-process run makes debugging output easier to read.
- Rector, PHPStan, Prettier, ESLint, and `vue-tsc` all persist content-keyed result caches (`storage/framework/cache/**` and `node_modules/.cache/**`), so an unchanged file is never analysed twice. CI restores the same caches.

## Architecture

### Backend Modules

`app/Modules`:

- `Marketing`
- `Auth`
- `Dashboard`
- `Settings`
- `Users`
- `Api/V1`
- `Shared`

Shared core model/enum:

- `app/Models/User.php`
- `app/Enums/UserRole.php`
- `app/Enums/Appearance.php`

Shared realtime infrastructure:

- `routes/channels.php`
- `app/Modules/Shared/Realtime`
- `app/Modules/*/Routes/channels.php`

### Frontend Modules

Pages are in:

- `resources/js/modules/marketing/pages`
- `resources/js/modules/auth/pages`
- `resources/js/modules/dashboard/pages`
- `resources/js/modules/settings/pages`
- `resources/js/modules/users/pages`

Shared UI primitives and layouts remain in:

- `resources/js/components`
- `resources/js/layouts`

Canonical frontend presentation contracts:

- `resources/css/theme.css` owns semantic light/dark colors, status tones, radii, shadows, glass/overlay values, gradients, and motion accessibility defaults.
- `resources/js/lib/theme.ts` owns typed reusable recipes for controls, surfaces, dialogs, sheets, menus, fields, tables, pagination, feedback, navigation, and animation.
- `resources/js/components/ui/**` owns low-level mechanics, while `resources/js/components/base/**` adds reusable application behavior.
- Icons resolve from Iconify collections through `unplugin-icons`; do not add runtime icon component packages or hand-authored replacements for library icons.
- Templates read the theme through a `const theme = appTheme` alias declared in `<script setup>`, because `vue-tsc` only typechecks auto-imported symbols that are bound in script.

## Route Contract

### Web

- `GET /` => `marketing.home`
- `GET /auth/login` => `auth.login.create`
- `POST /auth/login` => `auth.login.store`
- `GET /auth/register` => `auth.register.create`
- `POST /auth/register` => `auth.register.store`
- `POST /auth/logout` => `auth.logout`
- `GET /app/dashboard` => `app.dashboard`
- `GET /app/settings/profile` => `app.settings.profile.edit`
- `GET /app/settings/password` => `app.settings.password.edit`
- `GET /app/settings/appearance` => `app.settings.appearance`
- `GET /app/admin/users` => `app.admin.users.index`

### API v1

- `GET /api/v1/me` => `api.v1.me.show`
- `GET /api/v1/admin/users` => `api.v1.admin.users.index`
- `POST /api/v1/admin/users` => `api.v1.admin.users.store`
- `PUT /api/v1/admin/users/{user}` => `api.v1.admin.users.update`
- `DELETE /api/v1/admin/users/{user}` => `api.v1.admin.users.destroy`

## Type-Safe Data Contracts

The project uses Spatie Data + TypeScript Transformer 3, configured in `app/Providers/TypeScriptTransformerServiceProvider.php` (v3 has no config file).

Generated output:

- `resources/js/types/app-data.ts`

Generated route/action helpers:

- `resources/js/routes/**`
- `resources/js/actions/**`

Vite also generates the auto-import and component declarations at
`resources/js/types/auto-imports.d.ts` and `resources/js/types/components.d.ts`.
All generated artifacts are committed and validated by CI; do not hand-edit them.

Wayfinder's Windows/Linux newline handling is fixed by a local Composer patch, applied automatically by `composer install` (Git must be available). Commit `patches.lock.json` and `patches/**` alongside the dependency locks. When changing the patch, run `composer patches-relock` and `composer patches-repatch` before regeneration. Existing checkouts that already have Wayfinder installed should run `composer patches-repatch` once after pulling this change.

Nullable PHP properties generate required `T | null` fields, matching their serialized values. Use backend `Optional` or `Lazy` types for fields that may be absent; do not hide nulls by making every nullable property optional.

Realtime channel pattern enums, event-name enums, presence payloads, and broadcast payload DTOs are generated into the same `resources/js/types/app-data.ts` contract surface.

## Realtime

- Reverb is the default broadcaster in `.env.example`.
- Echo is initialized through `configureRealtime()`, called from both `resources/js/app.ts` and `resources/js/ssr.ts`. Under SSR it falls back to Echo's `null` broadcaster, so realtime pages render on the server without opening a connection.
- Channel authorization is module-local in `app/Modules/*/Routes/channels.php` and aggregated by the root `routes/channels.php`.
- Frontend modules should use shared realtime composables plus module-local `contracts/realtime.ts` helpers instead of using Echo directly.
- `apiRequest()` automatically forwards `X-Socket-ID` so broadcast listeners can call `toOthers()` safely.
- Queue workers should process `realtime,high,default` in that order.

## Rendering and Visual Stability

SSR delivers a fully rendered document, so anything the browser needs for the first
paint has to arrive as markup rather than as a side effect of the JS bundle:

- `resources/css/app.css` is its own Vite entry and is listed first in `@vite`, so it is a render-blocking stylesheet in dev and production. Importing it from `app.ts` instead would paint the server-rendered HTML unstyled and reflow once the bundle evaluated.
- The color scheme comes from the `appearance` cookie and is rendered onto `<html>` by Blade — no boot script, and no post-hydration re-apply. The same value is shared as an Inertia prop so the appearance controls render identically on both sides.
- Web fonts use `display=swap` and a pair of preconnects (the stylesheet fetch is same-origin to the font host, the font files are CORS, and they use separate connections).

## Security Defaults

- CSP + nonce-based security headers. The nonce is exposed to the client through a `meta[name="csp-nonce"]` tag and handed to Inertia so its injected style elements pass the policy.
- Hardened browser/security headers middleware.
- Throttling for sensitive auth endpoints.
- Email changes invalidate verification consistently across profile and admin updates.
- API mutations use Laravel's rotating CSRF cookie; automatic session headers stay on the same origin.
- Server-side authorization via policies and gates.

User-management side effects dispatch after successful persistence and transaction commit. The client clears cached account data when identity changes.

## Testing

Run full suite:

```bash
composer test
```

Backend tests use native Pest syntax with centrally configured compact output, fail-on-all-issues PHPUnit handling, level 9 PHPStan integration, official strict/deprecation rules, and architecture checks. `composer test` runs the suite in parallel, and CI runs the same command.

`phpunit.xml` points Laravel's configuration, route, and event cache paths at locations that never exist, so a locally cached boot manifest can never override the test environment. Extra arguments reach Pest directly, for example `composer test -- --filter=Registration`.

The mutating PHP cleanup order is Rector, then Pint, then PHPStan. `composer qa:check` mirrors that pipeline without changing files by using Rector dry-run and Pint test mode. CI generates backend-owned contracts before running the same checks.

Includes coverage for:

- auth flows
- settings flows
- admin users web flow
- API v1 user endpoints
- security headers
- dashboard + marketing rendering
- module discovery and generator behavior
- strict-types and security architecture rules

UI primitives participate in Vue typechecking and ESLint. Generator tests execute standalone and combined APIs with and without resources, including later pages and invalid pagination. Generated CRUD pages retain pagination metadata and controls.

CI also runs `composer audit --locked` and `npm audit`. The production PM2 example runs one scheduler and restarts services after graceful deployment exits.

## Working With Coding Agents

[AGENTS.md](AGENTS.md) is the canonical guide, with an entry map for each kind of change. `CLAUDE.md` imports the same guidance. Agents should trace the owning module and generated consumers, repair the complete flow, and finish with the documented quality gate and relevant tests.

See the [audit record](docs/starter-kit-audit.md) for verified repairs, coverage, and the limits of local validation.

## Notes

- Route names/URIs are intentionally domain-prefixed and breaking from legacy starter conventions.
- Inertia page resolution uses `resources/js/modules/**`.
- Keep changes modular and typed; avoid duplicated logic/UI.
