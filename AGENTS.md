# AGENTS.md

## Intent

This repository is a modular Laravel + Inertia + Vue starter kit optimized for strict type safety, security, and reusability.

## Mandatory Workflow

1. Understand current implementation and affected modules before writing code.
2. Reuse existing module services, requests, DTOs, and shared UI primitives before creating new abstractions.
3. Implement focused, typed changes with no duplication.
4. Run mandatory quality checks after every change:
   - `composer generate-and-cleanup`
   - `npm run typecheck`
   - targeted PHPUnit tests (or full `php artisan test` for broad changes)
5. Update this file when architecture, contracts, workflows, or enforcement rules change.

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
  - `routes/web.php` aggregates module web routes.
  - `routes/api.php` aggregates module API routes.
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

### Frontend

- Inertia pages live in `resources/js/modules/**/pages`.
- Shared app/layout primitives remain in:
  - `resources/js/components/**`
  - `resources/js/layouts/**`
- App entry points:
  - `resources/js/app.ts`
  - `resources/js/ssr.ts`

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

### Frontend

- Use `<script setup lang="ts">`.
- Keep strict TS (`noImplicitAny`, `strictNullChecks`, `exactOptionalPropertyTypes`).
- Avoid unsafe casts like `as User`; guard nullable values explicitly.
- Prefer named routes and generated helpers over hardcoded URIs.

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

## Reusability and Duplication Rules

- No duplicated business logic across modules.
- No duplicated large UI structures; extract shared components when repeated.
- No dead code, commented-out code, placeholder TODOs, or unused scaffolding.

## Quality Gate (Non-Negotiable)

Always run after changes:

```bash
composer generate-and-cleanup
npm run typecheck
php artisan test
```

## CI Compatibility

Local changes must remain compatible with existing CI checks:

- Pint / PHPStan / frontend format / frontend lint / TS typecheck / PHPUnit.