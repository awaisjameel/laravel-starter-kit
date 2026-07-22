# Laravel Modular Inertia Starter Kit

A Laravel 13 + Inertia 3 + Vue 3 starter kit with strict typed contracts from backend DTOs to frontend TypeScript.

## Requirements

- PHP 8.4+
- Node 24+ and npm 11+
- Composer 2+

## Stack

- Laravel 13, Inertia 3 (`inertiajs/inertia-laravel`, `@inertiajs/vue3`, `@inertiajs/vite`), Reverb 1.11, Sanctum 4
- Spatie Laravel Data 4 + TypeScript Transformer 3, Wayfinder, Ziggy
- Vue 3.5, TypeScript 6, Vite 8 (Rolldown), Tailwind CSS 4, Pinia 4, Reka UI, `@lucide/vue`
- PHPUnit 13, Vitest 4, Pint, PHPStan/Larastan, Rector, ESLint 10, Prettier 3

TypeScript is intentionally pinned to 6.x: TypeScript 7 does not yet expose the programmatic API that `vue-tsc` and `typescript-eslint` need.

## Quick Start

```bash
cp .env.example .env
composer install
npm install
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
    - `npm run typecheck`
    - `php artisan test`
    - `npm run test:unit`
- Non-mutating QA check:
    - `composer qa:check`
- Generated artifact sync check (mutating):
    - `composer generate`
    - `git diff --exit-code -- resources/js/actions resources/js/routes resources/js/types/app-data.ts resources/js/wayfinder/index.ts`

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

Never hand-edit generated files.

Realtime channel pattern enums, event-name enums, presence payloads, and broadcast payload DTOs are generated into the same `resources/js/types/app-data.ts` contract surface.

## Realtime

- Reverb is the default broadcaster in `.env.example`.
- Echo is initialized through `configureRealtime()`, called from both `resources/js/app.ts` and `resources/js/ssr.ts`. Under SSR it falls back to Echo's `null` broadcaster, so realtime pages render on the server without opening a connection.
- Channel authorization is module-local in `app/Modules/*/Routes/channels.php` and aggregated by the root `routes/channels.php`.
- Frontend modules should use shared realtime composables plus module-local `contracts/realtime.ts` helpers instead of using Echo directly.
- `apiRequest()` automatically forwards `X-Socket-ID` so broadcast listeners can call `toOthers()` safely.
- Queue workers should process `realtime,high,default` in that order.

## Security Defaults

- CSP + nonce-based security headers. The nonce is exposed to the client through a `meta[name="csp-nonce"]` tag and handed to Inertia so its injected style elements pass the policy.
- Hardened browser/security headers middleware.
- Throttling for sensitive auth endpoints.
- Server-side authorization via policies and gates.

## Testing

Run full suite:

```bash
php artisan test
```

Includes feature coverage for:

- auth flows
- settings flows
- admin users web flow
- API v1 user endpoints
- security headers
- dashboard + marketing rendering

## Notes

- Route names/URIs are intentionally domain-prefixed and breaking from legacy starter conventions.
- Inertia page resolution uses `resources/js/modules/**`.
- Keep changes modular and typed; avoid duplicated logic/UI.
