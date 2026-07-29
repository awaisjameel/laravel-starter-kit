# Laravel Modular Inertia Starter Kit

A Laravel 13 + Inertia 3 + Vue 3 starter kit with strict typed contracts from backend DTOs to frontend TypeScript.

## Requirements

- PHP 8.4+
- Node 24+ and npm 11+
- Composer 2+

Published Sail Docker contexts are limited to PHP 8.4 and 8.5, matching the Composer runtime constraint.

## Stack

- Laravel 13, Inertia 3 (`inertiajs/inertia-laravel`, `@inertiajs/vue3`, `@inertiajs/vite`), Reverb 1.11, Sanctum 4
- Spatie Laravel Data 4 + TypeScript Transformer 3, Wayfinder
- Vue 3.5, TypeScript 6, Vite 8 (Rolldown), Tailwind CSS 4, Pinia 4, Reka UI, `@lucide/vue`
- Pest 5 (PHPUnit 13 engine), Vitest 4, Pint, PHPStan/Larastan at `max` with strict/deprecation rules, Rector, ESLint 10, Prettier 3

TypeScript is intentionally pinned to 6.x: TypeScript 7 does not yet expose the programmatic API that `vue-tsc` and `typescript-eslint` need.

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
- Focused PHP tooling:
    - `composer refactor` / `composer refactor:check`
    - `composer format` / `composer format:check`
    - `composer analyse`
- Generated artifact sync check:
    - `composer generate`
    - `npm run build:ssr`

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

Never hand-edit generated files.

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
- Server-side authorization via policies and gates.

## Testing

Run full suite:

```bash
composer test
```

Backend tests use native Pest syntax with centrally configured compact output, fail-on-all-issues PHPUnit handling, maximum-level PHPStan integration, official strict/deprecation rules, and architecture checks. CI runs the same suite in parallel through `composer test:parallel`.

The mutating PHP cleanup order is Rector, then Pint, then PHPStan. `composer qa:check` mirrors that pipeline without changing files by using Rector dry-run and Pint test mode. CI generates backend-owned contracts, rejects any generated diff, and runs only non-mutating quality checks.

Includes coverage for:

- auth flows
- settings flows
- admin users web flow
- API v1 user endpoints
- security headers
- dashboard + marketing rendering
- module discovery and generator behavior
- strict-types and security architecture rules

## Notes

- Route names/URIs are intentionally domain-prefixed and breaking from legacy starter conventions.
- Inertia page resolution uses `resources/js/modules/**`.
- Keep changes modular and typed; avoid duplicated logic/UI.
