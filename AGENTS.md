<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

These rules are mandatory. Follow them exactly. If a rule conflicts with existing project conventions, ask before proceeding. Dev GUIDE: [READEME](./READEME.md)

## Foundational Context

This application is a Laravel application. You are an expert in the exact packages and versions below and must follow their version-specific best practices.

- php - 8.4.16
- inertiajs/inertia-laravel (INERTIA) - v2
- laravel/framework (LARAVEL) - v12
- laravel/prompts (PROMPTS) - v0
- laravel/sanctum (SANCTUM) - v4
- laravel/wayfinder (WAYFINDER) - v0
- tightenco/ziggy (ZIGGY) - v2
- laravel/mcp (MCP) - v0
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- phpunit/phpunit (PHPUNIT) - v12
- rector/rector (RECTOR) - v2
- @inertiajs/vue3 (INERTIA) - v2
- tailwindcss (TAILWINDCSS) - v4
- vue (VUE) - v3
- @laravel/vite-plugin-wayfinder (WAYFINDER) - v0
- eslint (ESLINT) - v9
- prettier (PRETTIER) - v3

## Non-Negotiable Conventions

- Follow existing code conventions exactly. Always review sibling files before editing or creating a file.
- Use descriptive, intention-revealing names. Example: `isRegisteredForDiscounts`, not `discount()`.
- Prefer reuse: find and use existing components, helpers, and types before writing new ones.
- Do not change dependencies, base folders, or the application structure without explicit approval.
- Do not create documentation unless explicitly requested.
- Be concise in explanations. Focus only on non-obvious decisions and trade-offs.

## Quality Gate (Strict)

- Code must be type-safe, reusable, and maintainable by default. Avoid implicit types, magic strings, and ambiguous APIs.
- Validate before marking complete: ensure static analysis, formatting, and tests (if applicable) are clean and relevant.
- Never leave dead code, TODOs, or commented-out logic.

## Verification Scripts

- Do not create verification scripts or tinker when tests already cover the behavior. Prefer unit/feature tests.

## Frontend Bundling

- If UI changes are not visible, ask the user to run `npm run build`, `npm run dev`, or `composer run dev`.

=== boost rules ===

## Laravel Boost

- Laravel Boost is an MCP server for this app. Use its tools when relevant. If an MCP tool fails, use an equivalent shell command.

## Artisan

- Use the `list-artisan-commands` tool before running `php artisan` to confirm available options and flags.
- Always pass `--no-interaction` to Artisan commands.

## URLs

- Use `get-absolute-url` when sharing project URLs.

## Tinker / Debugging

- Use `tinker` for PHP-level debugging or Eloquent queries.
- Use `database-query` when you only need to read from the database.

## Browser Logs

- Use `browser-logs` to inspect recent browser errors. Ignore stale logs.

## Searching Documentation (Mandatory)

- Use `search-docs` before making Laravel-ecosystem changes.
- Use multiple broad queries first. Do not include package names in queries.
- If unsure, search docs rather than guessing.

=== php rules ===

## PHP

- Always use curly braces for control structures.
- Follow modern PHP 8.4 best practices.

### Constructors

- Use constructor property promotion.
- Do not define an empty constructor.

`<code-snippet lang="php">`
public function \_\_construct(public GitHub $github)
{
}
`</code-snippet>`

### Type Declarations (Strict)

- All functions and methods must declare parameter and return types.
- Use nullable types and union types explicitly where needed.

`<code-snippet name="Explicit Return Types and Method Params" lang="php">`
protected function isAccessible(User $user, ?string $path = null): bool
{
// ...
}
`</code-snippet>`

## Comments

- Avoid inline comments. Use PHPDoc blocks only when complexity or static analysis requires it.

## PHPDoc Blocks

- Use PHPDoc for array shapes and complex generics when native types are insufficient.

## Enums

- Enum case names should be TitleCase.

=== tests rules ===

## Test Enforcement

- Every change must be validated before completion.
- Do not add tests unless explicitly requested.

=== inertia-laravel/core rules ===

## Inertia Core

- Place Inertia pages in `resources/js/Pages` unless Vite config dictates otherwise.
- Use `Inertia::render()` for server-side routing.
- Use `search-docs` for Inertia-specific guidance.

`<code-snippet lang="php" name="Inertia::render Example">`
// routes/web.php example
Route::get('/users', function () {
return Inertia::render('Users/Index', [
'users' => User::all(),
]);
});
`</code-snippet>`

=== inertia-laravel/v2 rules ===

## Inertia v2

- Use v2 features where they improve UX or performance; confirm usage via `search-docs` first.

### Inertia v2 New Features

- Polling
- Prefetching
- Deferred props
- Infinite scrolling via merging props and `WhenVisible`
- Lazy loading on scroll

### Deferred Props & Empty States

- When using deferred props, provide skeletons or loading states.

### Inertia Form Guidance

- Prefer `<Form>` for common use cases; use `useForm` when additional control is needed.
- Use `search-docs` for `<Form>` and `useForm` options.

=== laravel/core rules ===

## Do Things the Laravel Way

- Use `php artisan make:` commands to generate framework files.
- For generic PHP classes, use `php artisan make:class`.

### Database

- Use Eloquent relationships with proper return types.
- Prefer `Model::query()` and avoid `DB::` unless absolutely necessary.
- Prevent N+1 queries with eager loading.
- Use the query builder only for complex queries that Eloquent cannot express cleanly.

### Model Creation

- When creating models, also create factories and seeders if they are not present.
- Ask before adding additional files beyond the model, unless conventions already require them.

### APIs & Eloquent Resources

- Default to Eloquent API Resources and API versioning unless the project conventions differ.

### Controllers & Validation

- Always use Form Request classes for validation, including custom messages.
- Match existing convention for array vs. string rule syntax.

### Queues

- Use queued jobs with `ShouldQueue` for time-consuming work.

### Authentication & Authorization

- Use built-in authentication and authorization features (gates, policies, Sanctum).

### URL Generation

- Use named routes and `route()` for URL generation.

### Configuration

- Never call `env()` outside config files. Use `config()`.

### Testing

- Use model factories in tests.
- Follow existing conventions for `fake()` vs `$this->faker`.
- Use `php artisan make:test --phpunit` for test creation when needed.

### Vite Error

- For Vite manifest errors, run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== laravel/v12 rules ===

## Laravel 12 Structure

- No middleware files in `app/Http/Middleware/` (register in `bootstrap/app.php`).
- Use `bootstrap/app.php` for middleware, exceptions, and routing.
- Use `bootstrap/providers.php` for application service providers.
- No `app/Console/Kernel.php`.
- Commands in `app/Console/Commands/` auto-register.

### Database

- When modifying a column, include all existing attributes to avoid losing them.
- Laravel 11+ supports limiting eager loads with `$query->latest()->limit(10);`.

### Models

- Prefer `casts()` method over `$casts` when consistent with existing models.

=== wayfinder/core rules ===

## Laravel Wayfinder

Wayfinder generates TypeScript actions and types for Laravel routes.

### Development Guidelines

- Understand requirements and current implementation before changing code.
- Check sibling files for conventions.
- Always use `search-docs` for Wayfinder usage.
- Prefer named imports for tree-shaking.
- Avoid default controller imports.
- Run `php artisan wayfinder:generate` after route changes if the Vite plugin is not installed.

### Feature Overview

- Form Support: `store.form()` for `<Form>` attributes.
- HTTP Methods: `.get()`, `.post()`, `.patch()`, `.put()`, `.delete()`.
- Invokable Controllers: import and call as functions.
- Named Routes: import from `@/routes/`.
- Parameter Binding: accepts route keys via object or scalar.
- Query Merging: `mergeQuery` supports removal via `null`.
- Query Parameters: pass `{ query: {...} }`.
- Route Objects: return `{ url, method }`.
- URL Extraction: use `.url()`.

`<code-snippet name="Wayfinder Basic Usage" lang="typescript">`
// Import controller methods (tree-shakable)
import { show, store, update } from '@/actions/App/Http/Controllers/PostController';

show(1); // { url: "/posts/1", method: "get" }
show.url(1); // "/posts/1"
show.get(1); // { url: "/posts/1", method: "get" }
show.head(1); // { url: "/posts/1", method: "head" }

import { show as postShow } from '@/routes/post';
postShow(1); // { url: "/posts/1", method: "get" }
`</code-snippet>`

### Wayfinder + Inertia

Use Wayfinder with Inertia `<Form>` for action and method generation.

`<code-snippet name="Wayfinder Form Component (Vue)" lang="vue">`

<Form v-bind="store.form()">
    <input name="title" />
</Form>
`</code-snippet>`

=== pint/core rules ===

## Laravel Pint Code Formatter

- Run `composer generate-and-cleanup && npm run cleanup` before finalizing changes.
- Ensure those commands report no errors or warnings.

=== phpunit/core rules ===

## PHPUnit Core

- All tests are PHPUnit classes.
- Convert any Pest tests to PHPUnit if encountered.
- When updating a test, run only that test.
- Ask the user if they want the full test suite after relevant tests pass.
- Tests must cover happy paths, failure paths, and edge cases.
- Never delete tests without approval.

### Running Tests

- Run the minimal relevant set of tests.
- `php artisan test` (all tests).
- `php artisan test tests/Feature/ExampleTest.php` (single file).
- `php artisan test --filter=testName` (single test).

=== inertia-vue/core rules ===

## Inertia + Vue

- Vue components must have a single root element.
- Use `<Link>` or `router.visit()` for navigation.

`<code-snippet name="Inertia Client Navigation" lang="vue">`
import { Link } from '@inertiajs/vue3';

<Link href="/">Home</Link>
`</code-snippet>`

=== inertia-vue/v2/forms rules ===

## Inertia + Vue Forms

`<code-snippet name="<Form> Component Example" lang="vue">`

<Form
    action="/users"
    method="post"
    #default="{ errors, hasErrors, processing, progress, wasSuccessful, recentlySuccessful, setError, clearErrors, resetAndClearErrors, defaults, isDirty, reset, submit }"
>
    <input type="text" name="name" />

    <div v-if="errors.name">
        {{ errors.name }}
    </div>

    <button type="submit" :disabled="processing">
        {{ processing ? 'Creating...' : 'Create User' }}
    </button>

    <div v-if="wasSuccessful">User created successfully!</div>

</Form>
`</code-snippet>`

=== tailwindcss/core rules ===

## Tailwind Core

- Use Tailwind utility classes; match existing conventions.
- Extract repeated patterns into components where appropriate.
- Be deliberate about class placement, order, and redundancy.

### Spacing

- For lists, use gap utilities instead of margins.

`<code-snippet name="Valid Flex Gap Spacing Example" lang="html">`

<div class="flex gap-8">
    <div>Superior</div>
    <div>Michigan</div>
    <div>Erie</div>
</div>
`</code-snippet>`

### Dark Mode

- If the app supports dark mode, new UI must support it using `dark:` utilities.

=== tailwindcss/v4 rules ===

## Tailwind 4

- Use Tailwind v4 only. Do not use deprecated utilities.
- `corePlugins` is not supported in v4.
- Tailwind v4 is CSS-first using `@theme`.
- Import Tailwind with `@import "tailwindcss";`.

`<code-snippet name="Extending Theme in CSS" lang="css">`
@theme {
--color-brand: oklch(0.72 0.11 178);
}
`</code-snippet>`

`<code-snippet name="Tailwind v4 Import Tailwind Diff" lang="diff">`

- @tailwind base;
- @tailwind components;
- @tailwind utilities;

* @import "tailwindcss";
  `</code-snippet>`

### Replaced Utilities

- Do not use deprecated utilities. Use replacements below.

| Deprecated             | Replacement          |
| ---------------------- | -------------------- |
| bg-opacity-\_          | bg-black/\_          |
| text-opacity-\_        | text-black/\_        |
| border-opacity-\_      | border-black/\_      |
| divide-opacity-\_      | divide-black/\_      |
| ring-opacity-\_        | ring-black/\_        |
| placeholder-opacity-\_ | placeholder-black/\_ |
| flex-shrink-\_         | shrink-\_            |
| flex-grow-\_           | grow-\_              |
| overflow-ellipsis      | text-ellipsis        |
| decoration-slice       | box-decoration-slice |
| decoration-clone       | box-decoration-clone |

</laravel-boost-guidelines>
