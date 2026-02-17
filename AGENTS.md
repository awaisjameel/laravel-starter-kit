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
- Public client-facing UI is the **marketing domain** and must stay structurally separated from authenticated app domains (dashboard/settings/users/auth) except for approved shared primitives/utilities.
- For any component/layout/page in a subdirectory, use the namespace-prefixed auto-registered tag name (for example `<MarketingPageLayout />`, `<UsersCreateUserDialog />`), never an unscoped alias.
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
    - `UserPolicy` in `app/Policies/UserPolicy.php` enforces user management permissions
- Middleware:
    - `HandleAppearance` shares appearance from cookie
    - `HandleInertiaRequests` shares `name`, `quote`, `auth.user`, `ziggy`, `sidebarOpen`
    - `SecurityHeaders` appends CSP, frame, referrer, and browser-permission hardening headers
    - Cookie encryption excludes `appearance` and `sidebar_state`
- Validation:
    - Form Requests are required for user/auth/settings writes and filtered reads
- Persistence:
    - Users, password reset tokens, sessions, personal access tokens
    - Cache, job, batch, failed job tables
    - Default seeded users controlled by `APP_SEED_USERS`
    - Admin user-management actions are audit-logged via `App\Support\AuditLogger`

### Frontend

- Entry points:
    - `resources/js/app.ts` (CSR)
    - `resources/js/ssr.ts` (SSR)
- Inertia pages: `resources/js/pages` (lowercase directory is intentional), organized by domain namespaces:
    - `resources/js/pages/marketing/**` for public client-facing pages
    - `resources/js/pages/auth/**`, `resources/js/pages/settings/**`, `resources/js/pages/users/**` for authenticated/product flows
- Layouts:
    - `AppLayout` (sidebar shell for dashboard/app pages)
    - `AuthLayout` -> `AuthSimpleLayout` (single supported auth shell)
    - `marketing/PageLayout.vue` -> `MarketingPageLayout`
    - `settings/Layout.vue`
- Navigation and shell components:
    - `AppSidebar`, `AppShell`, `AppContent`, `NavMain`, `NavFooter`, `NavUser`, `UserMenuContent`
    - Marketing shell: `MarketingHeader`, `MarketingFooter`
    - Shared role-aware nav source: `resources/js/composables/useNavigation.ts`
- Domain component boundaries:
    - Marketing-only components: `resources/js/components/marketing/**`
    - App/dashboard shell and feature components: `resources/js/components/**` (excluding `marketing/**`)
    - Shared primitives: `resources/js/components/ui/**`
- Appearance/theming:
    - `useAppearance.ts` stores preference in `localStorage` and `appearance` cookie
    - Default appearance is light mode when no preference is set
    - Dark mode is class-based (`.dark`), initialized early in `resources/views/app.blade.php`
- UI component system:
    - App components: `resources/js/components`
    - UI primitives: `resources/js/components/ui`
    - Auto-registration enabled with namespaces (`directoryAsNamespace: true`)
    - For components in subdirectories, always use namespace-prefixed tags (example: `components/marketing/PageLayout.vue` => `<MarketingPageLayout />`, not `<PageLayout />`)
- Type declarations:
    - Shared page props and app types in `resources/js/types`
    - Typed Pinia stores belong in `resources/js/stores`

## Existing Features (What Agents Must Know)

- Marketing/public flow:
    - Route `/` renders `marketing/Welcome` via Inertia and stays outside auth middleware.
    - Marketing pages are public client-facing pages and must use the marketing namespace structure.
    - Marketing pages use `MarketingPageLayout` with shared sticky header/navigation and shared footer.
- Auth flows: registration, login, logout, forgot/reset password, confirm password, email verification.
- Settings flows: profile update, password update, appearance toggle, account deletion.
- Dashboard page behind `auth` + `verified` middleware.
- Dashboard/app pages use sidebar layout only via `AppLayout` (backed by `AppSidebarLayout`).
- User management (`/users`) for admins only:
    - Server-side pagination
    - Create, update, delete via dialogs
    - Role managed via `UserRole` enum
- API endpoint `/api/user` protected by Sanctum.

## Marketing Pages Standard (Public Client-Facing)

- Purpose:
    - Marketing pages are public entry points focused on product communication, conversion, and trust.
    - They must deliver premium, modern, and robust UX while remaining maintainable and type-safe.
- Required structure:
    - Pages: `resources/js/pages/marketing/**`
    - Layouts: `resources/js/layouts/marketing/**`
    - Components: `resources/js/components/marketing/**`
    - Route URIs remain public and unprefixed by default (for example `/`, `/about`, `/pricing`), and must not be moved under `/marketing/*` unless the user explicitly requests it.
    - Internal Inertia page namespace `marketing/*` is allowed and preferred for code organization without changing public URLs.
- Required namespacing:
    - Use namespace-prefixed tags for all nested files (for example `<MarketingPageLayout />`, `<MarketingHeroSection />`).
    - Keep marketing tags/components distinct from dashboard/auth/settings/users tags/components.
- Design-system enforcement:
    - Use shared tokens/utilities from `resources/css/app.css` and existing `resources/js/components/ui/**` primitives first.
    - Avoid one-off inline color systems in templates (raw hex/hsl classes) when tokenized utilities can express the same result.
    - Build reusable sections/components instead of repeating large class blocks across pages.
    - Maintain light/dark parity, semantic landmarks, keyboard/focus accessibility, and mobile-first responsiveness.
    - Marketing header/navigation must be sticky and shared through `MarketingPageLayout`.
- Cross-domain separation rules:
    - Marketing code must not depend on dashboard shell components (`AppShell`, `AppSidebar`, `AppSidebarLayout`, etc.).
    - Dashboard/auth/settings/users code must not depend on marketing-only components/layouts.
    - Shared logic belongs in neutral shared layers (`components/ui`, composables, typed DTO/types, lib helpers).
- Test expectations:
    - New or changed marketing routes must have feature tests for public accessibility and expected response behavior.
    - Behavior changes on marketing pages should include relevant frontend/feature coverage for edge states when applicable.

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
- Use named route helpers for `href` values (including breadcrumb/nav objects) instead of hard-coded path strings.
- Keep pages in `resources/js/pages` and map server renders exactly.
- All public client-facing pages must be under `resources/js/pages/marketing/**` and use `resources/js/layouts/marketing/**`.
- Reuse `resources/js/components/ui` primitives before adding new custom base components.
- All new UI must support both light and dark themes.

### Styling and UX

- Tailwind v4 only (`@import 'tailwindcss';` already configured).
- Reuse existing tokens and utilities in `resources/css/app.css`.
- Treat the design system as mandatory: prefer tokenized utilities (`bg-background`, `text-muted-foreground`, `border-border`, etc.) over one-off hard-coded palette classes.
- Keep class lists intentional and avoid redundant utility noise.
- Avoid duplicate UI structures; extract repeated blocks into typed, namespaced components/layouts.
- Prefer `gap-*` for spacing in lists/grouped layouts in all new or modified code; avoid introducing new `space-x-*`/`space-y-*` usage.

### Performance

- Paginate large datasets (follow `/users` pattern).
- Avoid unnecessary client state duplication of server props.
- Avoid unnecessary watchers/computeds when derived values are static.
- Keep payloads minimal and typed.

### Refactoring Automation (Rector)

- Central Rector config is `rector.php`.
- Rector runs over `app`, `bootstrap`, `config`, `database`, `public`, `routes`, and `tests`.
- Enabled Rector sets include dead code, code quality, coding style, type declarations/docblocks, privatization, naming, `instanceof`, early return, and PHP-version upgrades.
- Import names cleanup is enabled with unused import removal.
- Cache path is project-local at `storage/framework/cache/rector`.
- Keep `AddOverrideAttributeToOverriddenMethodsRector` skipped to avoid unsafe override attribute churn in framework-integrated classes.

## Validation and Quality Gate

**MANDATORY**: After any code change, run the single unified command:

```bash
composer generate-and-cleanup
```

This command automatically:

1. Generates TypeScript types from PHP DTOs (`php artisan typescript:transform`)
2. Generates Wayfinder route helpers (`php artisan wayfinder:generate`)
3. Fixes PHP code style with Pint (`pint --parallel`)
4. Refactors PHP with Rector (`rector process`)
5. Analyzes PHP with PHPStan (`phpstan analyse`)
6. Formats and lints frontend code (`npm run cleanup` = prettier + eslint --fix)

After running `composer generate-and-cleanup`, also run:

```bash
npm run typecheck
```

Then run targeted PHPUnit tests for changed behavior. If the change affects many areas, run full tests:

```bash
php artisan test
```

**Agents must NEVER skip running `composer generate-and-cleanup` after making code changes.**

## Laravel Boost MCP Workflow

When Laravel Boost MCP tools are available:

- Use `search-docs` before Laravel/Inertia/Wayfinder/Sanctum/Tailwind ecosystem changes.
- Use `list-artisan-commands` before running Artisan commands.
- Run Artisan with `--no-interaction` when the specific command supports it.
- Use MCP equivalents (`tinker`, `database-query`, `browser-logs`) when applicable.
  If MCP tools fail, use equivalent shell commands.

## Service Layer Pattern

- Purpose: Encapsulate business logic and complex operations that span multiple models or require external services.
- Location: `app/Services/`
- Naming: `{Entity}Service.php` (e.g., `UserService.php`)
- Usage: Inject into controllers via constructor dependency injection.
- When to use: Multi-model operations, external API integrations, complex data transformations, business rules enforcement.
- Keep controllers thin; delegate business logic to services.
- Services should be `final class` with explicit method types.

## Events and Listeners

- Events: `app/Events/` - Dispatch for decoupled operations.
- Listeners: `app/Listeners/` - Handle side effects asynchronously when possible.
- Register in `AppServiceProvider` or create dedicated `EventServiceProvider`.
- Use for: Audit logging, notifications, cache management, webhooks, search indexing.
- Example: `UserManagementEvent` with `LogUserManagementAudit` listener.
- Dispatch events after database transactions complete to avoid stale state.

## Queue Jobs

- Location: `app/Jobs/`
- Naming: `{Action}Job.php` (e.g., `SendWelcomeEmailJob.php`)
- Use for: Email sending, report generation, heavy computations, batch processing.
- Configure queue connections in `.env` (sync for local, database/redis for production).
- Dispatch with `dispatch(new SendWelcomeEmailJob($user))`.
- Jobs should implement `ShouldQueue` for background processing.
- Handle failures gracefully with `tries` and `backoff` properties.

## API Resources

- Location: `app/Http/Resources/`
- Use for API endpoint responses requiring transformation or pagination.
- Extend `Illuminate\Http\Resources\Json\JsonResource`.
- Keep DTOs for Inertia pages; use Resources for API endpoints.
- Return via `new UserResource($user)` or `UserResource::collection($users)`.
- Resources should transform data, not contain business logic.

## Testing Standards

- Location: `tests/Feature/` and `tests/Unit/`
- Naming: `{Feature}Test.php` using PascalCase.
- All test files must use `declare(strict_types=1);`.
- Use `RefreshDatabase` trait for database tests.
- Test structure per method:
    - Happy path (success scenarios)
    - Failure path (validation, authorization)
    - Edge cases (boundaries, empty states)
- Use factories: `User::factory()->create(['role' => UserRole::Admin])`.
- Assert against database state and response content.
- Feature tests should cover routes, policies, and validation.
- Unit tests for isolated logic (services, helpers, custom functions).

### Minimum Test Requirements

Every new feature or behavior change MUST include tests. Agents must never skip test writing:

1. **New Routes**: Must have feature tests for:
    - Successful response (happy path)
    - Authentication requirements (if protected)
    - Authorization requirements (if role-based)
    - Validation errors (if accepting input)

2. **New Controllers/Methods**: Must have tests for:
    - Happy path scenario
    - Validation failures
    - Authorization failures (if applicable)

3. **New Services**: Must have unit tests for:
    - Public method behavior
    - Edge cases and error handling

4. **New Policies**: Must have feature tests verifying:
    - Authorized users can access
    - Unauthorized users cannot access

5. **Bug Fixes**: Must include regression test that:
    - Reproduces the original bug
    - Verifies the fix works

### Test Coverage Expectations

- Controllers: Test all public methods
- Services: Test all public methods with edge cases
- Policies: Test all ability methods
- Form Requests: Test validation rules via controller tests
- Middleware: Test behavior with various conditions

### Running Tests

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/Users/UserManagementTest.php

# Run with coverage (if configured)
php artisan test --coverage
```

## Route Helper Conventions

### Backend

- Use `route('name')` helper or `to_route('name')` for redirects.
- Use named routes exclusively, never hard-code URLs.
- Route model binding automatically resolves models.

### Frontend

- Import Wayfinder route helpers for form submissions: `import { store } from '@/routes/users'`.
- Use Ziggy `route()` for navigation links and breadcrumbs.
- Wayfinder provides type-safe URL generation with parameters.
- Example form submission: `form.post(store().url)` or `form.submit(users.store())`.

## Form Component Patterns

- Use `UiInput`, `UiLabel`, `UiSelect` from `components/ui/`.
- Wrap form fields in `<div class="grid gap-2">` containers.
- Display errors via `<InputError :message="form.errors.field" />`.
- Use Inertia's `useForm()` for form state management.
- Common pattern:
    ```vue
    <div class="grid gap-2">
        <UiLabel for="email">Email</UiLabel>
        <UiInput id="email" type="email" v-model="form.email" />
        <InputError :message="form.errors.email" />
    </div>
    ```

## Flash Messages

- Set via `redirect()->route('name')->with('message', 'Success text')`.
- Standard keys: `message` (success), `error` (errors), `status` (status updates).
- Access in Vue via `page.props.message` or passed props.
- Keep messages concise and user-friendly.
- Use session flash for one-time notifications across redirects.

## Reusable Component Standards

### Frontend Component Architecture

- **Base Components** (`resources/js/components/ui/`):
    - Pure, presentational components with no business logic.
    - Accept props for all configurable values.
    - Emit events for parent components to handle.
    - Support both light and dark themes.
    - Examples: `UiButton`, `UiInput`, `UiSelect`, `UiCard`, `UiDialog`.

- **Form Components** (`resources/js/components/form/`):
    - Wrap base UI components with label/error handling.
    - Use consistent `<div class="grid gap-2">` container pattern.
    - Examples: `FormField`, `FormSelect`, `FormTextarea`, `FormCheckbox`.

- **Feature Components** (`resources/js/components/{domain}/`):
    - Domain-specific components with business logic.
    - Use composables for shared behavior.
    - Examples: `users/CreateUserDialog`, `users/EditUserDialog`.

- **Layout Components** (`resources/js/layouts/`):
    - Page shell components for consistent structure.
    - Examples: `AppLayout`, `AuthLayout`, `MarketingPageLayout`.

### When to Create New Components

1. **Extract when duplicated 2+ times**: If similar UI structure appears in 2+ places, create a reusable component.
2. **Single responsibility**: Each component should do one thing well.
3. **Prop-driven flexibility**: Use props for variations, not separate components.
4. **Slot-based composition**: Use slots for content injection points.

### Component Naming Conventions

- **Base UI**: `Ui{Name}` (e.g., `UiButton`, `UiInput`)
- **Form**: `Form{Field}` (e.g., `FormField`, `FormSelect`)
- **Feature**: `{Domain}{Action}` (e.g., `UsersCreateDialog`)
- **Layout**: `{Domain}Layout` (e.g., `AppLayout`, `MarketingPageLayout`)

## Composables Pattern

### Purpose and Location

- Location: `resources/js/composables/`
- Naming: `use{Feature}.ts` (e.g., `useToast`, `useNavigation`, `useAppearance`)
- Export as composable function, not object.

### When to Create Composables

1. **Stateful logic reuse**: Same reactive state/logic needed in multiple components.
2. **Complex operations**: Encapsulate complex calculations or transformations.
3. **API/data fetching**: Centralize data fetching patterns.
4. **Cross-cutting concerns**: Toast notifications, modals, loading states.

### Composable Best Practices

```typescript
// Good: Returns reactive state and methods
export function useToast() {
    const toasts = ref<Toast[]>([])

    const show = (message: string, type: ToastType = 'success') => {
        toasts.value.push({ id: Date.now(), message, type })
    }

    return { toasts: readonly(toasts), show }
}

// Bad: Returns only methods, no reactive state
export function useToast() {
    return {
        show: (message: string) => { /* ... */ }
    }
}
```

### Existing Composables

- `useAppearance`: Theme/appearance management (light/dark mode).
- `useNavigation`: Role-aware navigation items.
- `useInitials`: Generate user initials from name.
- `useToast`: Toast notification system.
- `useHttp`: API HTTP client (for non-Inertia API calls).

## Utility Functions

### Location and Purpose

- Location: `resources/js/lib/`
- Naming: Descriptive function names, `{domain}Utils.ts` for domain-specific utilities.
- Main file: `utils.ts` for shared utilities.

### When to Create Utilities

1. **Pure functions**: No side effects, same input = same output.
2. **Data transformation**: Format dates, parse strings, transform objects.
3. **Validation helpers**: Client-side validation functions.
4. **Constants**: Shared configuration values.

### Utility Examples

```typescript
// resources/js/lib/utils.ts
export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs))
}

export function getEnumOptions<TEnum extends Record<string, string>>(
    enumType: TEnum
): Array<{ value: string; label: string }> {
    return Object.values(enumType).map((value) => ({ value, label: value }))
}

export function formatDate(date: string | Date, format: string = 'medium'): string {
    // Date formatting logic
}
```

## Backend Service Layer

### Service Standards

- Location: `app/Services/`
- Naming: `{Entity}Service.php` (e.g., `UserService.php`)
- Always use `final class` with explicit types.
- Inject via constructor dependency injection.

### When to Create Services

1. **Multi-model operations**: Operations involving multiple Eloquent models.
2. **External integrations**: API calls, file storage, third-party services.
3. **Complex business logic**: Rules that don't belong in models or controllers.
4. **Reusable operations**: Same logic needed in multiple controllers/commands.

### Service Pattern Example

```php
<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Events\UserManagementEvent;
use Illuminate\Http\Request;

final class UserService
{
    public function createUser(array $data, User $actor, Request $request): User
    {
        $user = User::create($data);

        Event::dispatch(new UserManagementEvent(
            action: 'create',
            actor: $actor,
            target: $user,
            request: $request,
        ));

        return $user;
    }
}
```

### Controller-Service Relationship

- Controllers handle HTTP: validation, authorization, response.
- Services handle business: data operations, events, external calls.
- Keep controllers thin; delegate to services.

## Artisan Commands

### Location and Purpose

- Location: `app/Console/Commands/`
- Naming: `{Action}{Entity}` (e.g., `ImportUsers`, `GenerateReport`)
- Register in `routes/console.php`.

### When to Create Commands

1. **Scheduled tasks**: Cron jobs, periodic maintenance.
2. **Data imports/exports**: Bulk data operations.
3. **One-time migrations**: Data transformations.
4. **Administrative tasks**: User management, cache clearing.

### Command Pattern Example

```php
<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\UserService;
use Illuminate\Console\Command;

final class ImportUsers extends Command
{
    protected $signature = 'users:import {file}';
    protected $description = 'Import users from CSV file';

    public function handle(UserService $userService): int
    {
        $file = $this->argument('file');
        // Import logic using service

        return self::SUCCESS;
    }
}
```

## Shared Data Components

### Pagination Component

- Use `PaginationNav` component for server-side pagination.
- Accepts `Paginated<T>` type from `@/types`.
- Emits `page-change` event for navigation.

```vue
<PaginationNav :pagination="users" @page-change="onPageChange" />
```

### Data Table Component

- Use `DataTable` component for tabular data.
- Accepts columns definition and data array/paginated data.
- Supports custom cell slots and row actions.

```vue
<DataTable :columns="columns" :data="users" :actions="tableActions" @row-click="onRowClick">
    <template #cell-name="{ row }">
        <UserInfo :user="row" />
    </template>
</DataTable>
```

## Toast Notification System

### Usage Pattern

```typescript
import { useToast } from '@/composables/useToast'

const { success, error, info, warning } = useToast()

// In form success callback
form.post(route('users.store'), {
    onSuccess: () => {
        success('User created successfully')
    },
    onError: () => {
        error('Failed to create user')
    }
})
```

### Toast Types

- `success`: Green, checkmark icon - successful operations.
- `error`: Red, X icon - failures and errors.
- `info`: Blue, info icon - informational messages.
- `warning`: Yellow, warning icon - warnings and cautions.

## Code Reusability Checklist

Before creating new code, verify:

### Frontend

- [ ] Check if existing UI component can be extended.
- [ ] Check if composable exists for shared logic.
- [ ] Check if utility function exists for transformation.
- [ ] Use shared form components instead of raw inputs.
- [ ] Use shared data components for tables/pagination.
- [ ] Extract repeated patterns into reusable components.

### Backend

- [ ] Check if service exists for business logic.
- [ ] Check if event/listener pattern applies.
- [ ] Check if job should be used for background processing.
- [ ] Use Form Requests for validation.
- [ ] Use Policies for authorization.
- [ ] Use DTOs for data transfer.

## Known Caveats (Current Starter Kit State)

- Validate tooling and version assumptions against `composer.json` and `package.json` before adding workflows or scripts.

## CI Expectations

GitHub Actions currently run:

- Lint workflow: Pint, frontend format, frontend lint
- Test workflow: build assets and run PHPUnit
  Agents should keep local changes compatible with those checks.
