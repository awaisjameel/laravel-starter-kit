# Laravel Secure and Type Safe Starter Kit

A Laravel 12 + Inertia + Vue 3 starter kit focused on security and type safety. It ships with ShadcnVue components, Tailwind CSS v4, typed DTOs and enums shared with the frontend, and generated route helpers so backend and frontend stay in sync.

## Table of contents

- Quick start
- Environment configuration
- Project overview
- Directory map
- Backend details
- Frontend details
- Type safety and code generation
- Routing and navigation
- UI and styling
- Example flow: user management
- Development workflows
- Testing
- Build and deploy
- Troubleshooting

## Quick start

### Requirements

- PHP 8.4+
- Node 24+ and npm 11+ (npm only; preinstall check enforces this)
- SQLite (default) or another supported database

### Install

1. Copy environment file and adjust values for your machine:
    - `cp .env.example .env`
2. Install dependencies:
    ```bash
    composer install
    npm install
    ```
3. Generate the application key:
    ```bash
    php artisan key:generate
    ```
4. Ensure the SQLite database file exists if you use SQLite:
    ```bash
    # The repo includes database/database.sqlite, create it if missing.
    php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
    ```
5. Run migrations:
    ```bash
    php artisan migrate
    ```

### Run the app

Option A (one command):

```bash
composer dev
```

This runs `php artisan serve`, `php artisan queue:listen --tries=1`, `php artisan pail --timeout=0`, and `npm run dev` concurrently.

Option B (separate terminals):

```bash
php artisan serve
php artisan queue:listen --tries=1
php artisan pail --timeout=0
npm run dev
```

Then open:

- `http://localhost:8000`

### Default users

If `APP_SEED_USERS=true`, the users table migration seeds two accounts:

```
Admin: admin@app.com / Admin123!@#
User:  user@app.com  / User123!@#
```

Set `APP_SEED_USERS=false` in production.

### Optional: SSR

```bash
composer dev:ssr
```

### Optional: Laravel Sail (Docker)

```bash
./sail up -d
./sail composer install
./sail npm install
./sail artisan migrate
```

## Environment configuration

Key env values used by the starter kit:

- `APP_URL` controls app URL and is used by Ziggy.
- `VITE_APP_NAME` sets the browser title (see `resources/js/app.ts`).
- `VITE_APP_URL` is used by `resources/stores/useHttp.js` as the API base URL.
- `APP_SEED_USERS` toggles default user seeding in the users migration.
- `DB_CONNECTION` defaults to `sqlite`.
- `QUEUE_CONNECTION` defaults to `database`, and `SESSION_DRIVER` defaults to `database`.

## Project overview

- Backend: Laravel 12 with Sanctum and Inertia server responses.
- Frontend: Vue 3 + Inertia pages in `resources/js/pages`.
- UI: ShadcnVue components built on Reka UI + Tailwind CSS v4.
- Type safety: Spatie Data and enums generate TypeScript definitions.
- Routing: Wayfinder generates typed route helpers; Ziggy provides `route()` in Vue.

## Directory map

Key locations and what they do:

- `app/Data/` - DTOs (Spatie Data) exported to TypeScript.
- `app/Enums/` - Backend enums (exported to TypeScript).
- `app/Http/Controllers/` - Inertia controllers and settings/auth flows.
- `app/Http/Requests/` - Form Request validation.
- `app/Http/Middleware/` - Inertia and appearance middleware.
- `app/Models/` - Eloquent models.
- `app/Providers/` - Service providers and authorization gates.
- `bootstrap/app.php` - Route and middleware registration.
- `config/` - Framework and package configuration.
- `database/migrations/` - Schema and seed logic.
- `resources/views/app.blade.php` - Inertia root view.
- `resources/js/app.ts` - Inertia/Vue entry point.
- `resources/js/ssr.ts` - Inertia SSR entry point.
- `resources/js/pages/` - Inertia pages (lowercase folder is intentional).
- `resources/js/layouts/` - Layouts for app, auth, and settings.
- `resources/js/components/` - App-specific Vue components.
- `resources/js/components/ui/` - ShadcnVue UI components.
- `resources/js/composables/` - Shared hooks like appearance and initials.
- `resources/js/lib/` - Utilities (`cn`, enum helpers).
- `resources/js/routes/` - Generated named route helpers (Wayfinder).
- `resources/js/actions/` - Generated controller route helpers (Wayfinder).
- `resources/js/types/` - Shared TS types and generated declarations.
- `resources/css/app.css` - Tailwind v4 theme, variables, and base styles.
- `routes/` - Web, auth, settings, and API route definitions.
- `tests/` - PHPUnit tests.

## Backend details

### Routing

- `routes/web.php` defines:
    - `/` -> `Welcome` page
    - `/dashboard` -> `Dashboard` page (auth + verified)
    - `/users` resource routes (admin only)
- `routes/auth.php` covers login, password reset, and email verification. Registration routes are commented out by default.
- `routes/settings.php` covers profile, password, and appearance settings.
- `routes/api.php` exposes `/api/user` via Sanctum.

### Authentication and authorization

- Authorization uses a gate in `app/Providers/AuthServiceProvider.php`:
    - `manage-users` allows only `UserRole::Admin`.
- Navigation (header and sidebar) checks `UserRole` to show admin-only links.
- Registration routes exist but are disabled by default.

### Models and data

- `app/Models/User.php`:
    - `role` is cast to `UserRole` enum.
    - `password` is cast to `hashed`.
    - `toData()` returns `UserData` for consistent typing.
- `app/Data/UserData.php`:
    - Defines a DTO with validation rules and TypeScript export (`#[TypeScript]`).
- `app/Enums/UserRole.php`:
    - `Admin` and `User` roles exported to TypeScript.
- `app/Http/Controllers/UserController.php`:
    - Uses `UserCreateRequest` and `UserUpdateRequest`.
    - Returns paginated `UserData` to the frontend.

### Middleware and shared props

- `app/Http/Middleware/HandleAppearance.php` reads the `appearance` cookie for theme.
- `app/Http/Middleware/HandleInertiaRequests.php` shares:
    - `auth.user`, `ziggy`, `quote`, and `sidebarOpen`.
- `bootstrap/app.php` registers middleware and excludes `appearance` and `sidebar_state` from cookie encryption for frontend access.

### Database and seeding

- Default DB is SQLite with `database/database.sqlite`.
- The users migration seeds two accounts when `APP_SEED_USERS=true`.
- Jobs, cache, and session tables are created by migrations for the default drivers.

## Frontend details

### App bootstrap

- `resources/js/app.ts`:
    - Bootstraps Inertia, Pinia, and Ziggy.
    - Initializes the appearance theme on load.
- `resources/js/ssr.ts`:
    - SSR entry point used by `composer dev:ssr`.

### Pages and layouts

- Inertia pages are in `resources/js/pages/`.
    - Example: `Inertia::render('users/Index')` -> `resources/js/pages/users/Index.vue`.
- Layouts live in `resources/js/layouts/`:
    - `AppLayout`, `AppSidebarLayout`, `AuthLayout`, `SettingsLayout`.

### Components

- App components: `resources/js/components/`.
- User dialogs: `resources/js/components/users/`.
- ShadcnVue UI: `resources/js/components/ui/`.
- Auto component registration is enabled with `directoryAsNamespace: true`, so:
    - `resources/js/components/users/CreateUserDialog.vue` becomes `<UsersCreateUserDialog />`.

### State and composables

- `resources/js/composables/useAppearance.ts` handles dark mode (localStorage + cookie).
- `resources/js/composables/useInitials.ts` provides initials for avatars.
- Pinia store `resources/stores/useHttp.js` is a simple fetch wrapper:
    - Uses `VITE_APP_URL` as the API base URL.

### Shared types

- `resources/js/types/index.d.ts` defines `AppPageProps`, `User`, pagination types, and nav types.
- Keep it in sync with `HandleInertiaRequests::share`.

## Type safety and code generation

### PHP to TypeScript types (Spatie)

- `app/Data` and `app/Enums` are exported to `resources/js/types/app-data.ts`.
- Run after DTO/enum changes:
    ```bash
    composer generate
    ```

### Route helpers (Wayfinder)

- Generated named route helpers live in `resources/js/routes/`.
- Generated controller helpers live in `resources/js/actions/`.
- Run after route changes:
    ```bash
    php artisan wayfinder:generate
    ```
- The Vite Wayfinder plugin also runs this on dev start:
    - See `vite.config.ts`.

### Auto-imports

- `unplugin-auto-import` and `unplugin-vue-components` generate:
    - `resources/js/types/auto-imports.d.ts`
    - `resources/js/types/components.d.ts`
- These files are generated. Do not edit by hand.

## Routing and navigation

- Ziggy registers `route()` in Vue (via `ZiggyVue`).
- Wayfinder provides typed helpers for URLs and methods.

Examples:

```typescript
import { index } from '@/routes/users'
import { store } from '@/actions/App/Http/Controllers/UserController'

index.url() // "/users"
store() // { url: "/users", method: "post" }
```

Use named imports for tree-shaking where possible.

## UI and styling

- Tailwind CSS v4 is configured in `resources/css/app.css`.
    - Theme tokens are defined using `@theme` and CSS variables.
    - Dark mode uses the `.dark` class.
- ShadcnVue components live in `resources/js/components/ui`.
    - Many components use Reka UI primitives and `class-variance-authority`.
- Icons:
    - `lucide-vue-next` is used directly in components.
    - Iconify icons are auto-registered with the `Icon` prefix:
        - Example: `<Icon-mdi-home />`.

## Example flow: user management

1. Admin visits `/users` -> `UserController@index` returns paginated `UserData`.
2. Inertia renders `resources/js/pages/users/Index.vue` with typed props.
3. Dialogs use `useForm` and Wayfinder helpers (`users.store()`, `users.update()`, `users.destroy()`).
4. `UserCreateRequest` and `UserUpdateRequest` validate input, then `User` is created/updated with enum and hashed password casts.

## Development workflows

### Generate types and routes

```bash
composer generate
```

Includes:

- `php artisan typescript:transform`
- `php artisan wayfinder:generate`

### Formatting and cleanup

```bash
composer cleanup
npm run cleanup
```

Run both before opening a pull request to keep backend and frontend code clean and consistent. `composer cleanup` runs Pint, Rector, and Prettier, while `npm run cleanup` formats and lints frontend files.

### Frontend linting and type checks

```bash
npm run lint
npm run typecheck
```

### Dev servers

```bash
composer dev
```

or

```bash
npm run dev
php artisan serve
```

## Testing

Run the test suite:

```bash
php artisan test
```

## Build and deploy

Typical production steps:

```bash
composer install --no-dev --optimize-autoloader
npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
```

## Troubleshooting

- Vite manifest error: run `npm run dev` or `npm run build`.
- Missing routes or stale helpers: run `composer generate`.
- Users menu not visible: log in as the seeded admin or grant `UserRole::Admin`.
- Theme not applying: clear `appearance` in localStorage and cookies.
- API base URL undefined: set `VITE_APP_URL` in `.env`.
