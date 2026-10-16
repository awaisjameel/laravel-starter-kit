# Module Generator

Read before adding a module or page, running `generate:module`, or changing `stubs/module-generation`, the scaffold planner, or `app/Modules/Shared/Support`.

## Command

```bash
php artisan generate:module <ModuleName> --scaffold=crud --page=<PageName>
php artisan generate:module <ModuleName> --extend --scaffold=page --page=<PageName>
```

- Supported `--scaffold` targets:
    - `page` - Frontend-only page contracts
    - `crud` - Full backend + frontend CRUD
    - `api` - Backend API only
    - `crud-api` - Full CRUD with both web and API
- `--page` names the initial page (default `Index`). Module names accept PascalCase, kebab-case, snake_case, or path-like input.
- `--extend` requires the module to already exist. Fresh mode fails if the module already exists.
- Interactive shells prompt for scaffold/profile choices and, unless disabled, per-file generation confirmation.
- Use `--no-file-prompts` to skip per-file confirmations.
- Use `--dry-run` to inspect the plan without writing files.
- Use `--force` to overwrite existing generated files.
- Use `--base-path` only for tests or isolated generation scenarios.
- Command options contract:
    - `--route-profile=app|public|custom` (non-interactive defaults to `app`)
    - `--roles=all|admin,user` (required when scaffolding includes app CRUD web routes or protected API routes; supports `all` or comma-separated values from `App\Enums\UserRole`)
    - `--route-prefix=...` and `--route-name-prefix=...`
    - `--middleware=auth,verified`
    - `--api-route-profile=protected|public|custom` (non-interactive defaults to `protected`)
    - `--api-route-prefix=...` and `--api-route-name-prefix=...`
    - `--api-middleware=auth:sanctum`
    - `--no-api-resource`, `--no-api-test`, `--no-model`, `--no-page`

## Adding A Page

1. Scaffold it with the command above: `crud` (or `api`/`crud-api`) for a new resource, `--extend --scaffold=page` for a page in an existing module.
2. A `page` scaffold generates no backend routes, so connect it yourself: add or extend a controller in `app/Modules/<Module>/Http/Controllers`, add the route to the module's `Routes/web.php` or `Routes/api.php` (the [registry](backend.md#module-discovery) discovers it), and return the page through `PageResponder::render('modules/<module>/pages/<Page>')`. Then replace the scaffold's frontend-only form values as described under [generator behavior](#generator-behavior).
3. Build the page from typed `defineProps<...>()`, a shared layout (`AppLayout`, `AuthLayout`, `MarketingPageLayout`), and `Base*` components. Forms follow [forms](frontend-ui.md#forms), listings follow [server tables](frontend-ui.md#server-tables), and navigable pages register in [navigation](frontend.md#navigation-and-breadcrumbs).

## Generator Behavior

- Template rendering rejects missing tokens before writes; module and page identifiers must start with a letter after normalization.
- Rendered PHP stubs are syntax-checked and reject declaration/import name collisions (for example a model named `Model`) before writes. Keep top-level class imports on individual lines so validation and import sorting share the same convention.
- Frontend scaffolds use `AppLayout` only when their configured middleware contains `auth` or `auth:*`; otherwise they use `MarketingPageLayout`. The `page` scaffold honors explicit route-profile and middleware options for layout selection without generating backend routes. Custom profiles retain authenticated middleware by default.
- CRUD/API model names also reject PHP reserved names before files are written.
- Generated listings use `PaginationQueryRequest` / `PaginationQueryData`, bounded to 100 rows per page, with stable ordering. CRUD page DTOs include shared `PaginationData` metadata and the page uses the shared pagination component. Extend the module's query DTO/request when adding filters or sorting.
- Every paginated page DTO exposes the same envelope: `items` plus a shared `PaginationData`. `UsersIndexPageData` follows it too, so the reference module and generator output have one shape. Do not redeclare paginator metadata per module.
- Resource API pagination links preserve query parameters. Plain API metadata uses `PaginationData` too.
- `TemplateRenderer` sorts the rendered `use` block through `PhpUseStatementSorter`. A stub cannot know whether `App\Modules\Shared\...` sorts before or after the module namespace, so imports are ordered after rendering instead. Stubs stay Rector- and Pint-clean by construction: name parameters after their type, and keep generated arrays free of trailing commas.
- A `page` scaffold has frontend-only form values until connected to a backend endpoint. When connecting it, derive form values from that endpoint's generated DTO with `FormValuesFromData`. It must never import a DTO that the scaffold did not generate. CRUD scaffolds already do this.
- `GeneratedPaginationTest` executes all four API variants (standalone/combined, resource/plain) and checks later pages, invalid input, and CRUD page metadata.
- Frontend stubs follow the [theme contracts](frontend-ui.md#theme-contracts); backend test stubs follow the [testing](testing.md#backend-pest) conventions.

## Scaffold Outputs

- `page` scaffolds frontend-only page contracts:
    - `resources/js/modules/<module>/forms/<page-kebab>-form-schema.ts`
    - `resources/js/modules/<module>/pages/<Page>.vue`
    - `resources/js/modules/<module>/pages/__tests__/<Page>.test.ts`
- `crud` scaffolds:
    - module-local requests, DTOs, queries, commands
    - web controller
    - web routes
    - page/list DTOs
    - resource manifest
    - optional gate file depending on role scope
    - model + migration unless skipped
    - frontend CRUD page/contracts/components unless `--no-page`
    - feature test
- `api` scaffolds:
    - module-local requests, DTOs, queries, commands
    - API controller
    - API routes
    - optional `JsonResource`
    - model + migration unless skipped
    - API feature test unless skipped
- `crud-api` scaffolds both web and API layers and additionally generates module-local handlers under `app/Modules/<Module>/Handlers/**`.

### Generated CRUD Frontend Files

- `resources/js/modules/<module>/contracts/<page-kebab>-crud.ts`
- `resources/js/modules/<module>/contracts/dashboard-nav.ts`
- `resources/js/modules/<module>/forms/<page-kebab>-form-schema.ts`
- `resources/js/modules/<module>/components/Table.vue`
- `resources/js/modules/<module>/components/<Page>FormDialog.vue`
- `resources/js/modules/<module>/components/<Page>DeleteDialog.vue`
- `resources/js/modules/<module>/components/<Page>DetailsDialog.vue`
- `resources/js/modules/<module>/pages/<Page>.vue`
- `resources/js/modules/<module>/pages/__tests__/<Page>.test.ts`

### Generated CRUD Backend Files

- `app/Modules/<Module>/Http/Controllers/<Page>Controller.php`
- `app/Modules/<Module>/Http/Requests/<Page>StoreRequest.php`
- `app/Modules/<Module>/Http/Requests/<Page>UpdateRequest.php`
- `app/Modules/<Module>/Data/<Module><Page>StoreData.php`
- `app/Modules/<Module>/Data/<Module><Page>ListItemData.php`
- `app/Modules/<Module>/Data/<Module><Page>PageData.php`
- `app/Modules/<Module>/Queries/<Model>Queries.php`
- `app/Modules/<Module>/Commands/<Model>Commands.php`
- `app/Modules/<Module>/Manifests/<Page>Resource.php`
- `app/Modules/<Module>/Routes/web.php`
- `app/Modules/<Module>/Routes/gates.php` when generated app CRUD or protected API routes are role-restricted
- `tests/Feature/<Module>/<Page>PageTest.php`
- `app/Models/<Model>.php` unless skipped
- `database/migrations/*_create_<table>_table.php` unless skipped

### Generated API Backend Files

- `app/Modules/<Module>/Http/Controllers/<Page>ApiController.php`
- `app/Modules/<Module>/Routes/api.php`
- `app/Modules/<Module>/Http/Resources/<Page>Resource.php` unless `--no-api-resource`
- `tests/Feature/<Module>/<Page>ApiTest.php` unless `--no-api-test`

## Route Defaults

- Web `app` profile:
    - default prefix: `app/<module-kebab>`
    - default name prefix: `app.<module-kebab>`
    - default middleware: `auth`, `verified`
- Web `app` profile with admin-only role scope:
    - default prefix: `app/admin/<module-kebab>`
    - default name prefix: `app.admin.<module-kebab>`
    - default middleware: `auth`, `verified`, `can:manage-<module-kebab>`
- Web `public` profile:
    - default prefix: `<module-kebab>`
    - default name prefix: `<module-kebab>`
    - default middleware: none
- API `protected` profile:
    - default module route prefix: `v1/admin/<module-kebab>` (Laravel applies the external `/api` prefix)
    - default name prefix: `api.v1.admin.<module-kebab>`
    - default middleware: `auth:sanctum`, plus `can:manage-<module-kebab>` for role-restricted scopes
- API `public` profile:
    - default module route prefix: `v1/<module-kebab>` (Laravel applies the external `/api` prefix)
    - default name prefix: `api.v1.<module-kebab>`
    - default middleware: none

## Resource Manifests

- Generated CRUD resources own a manifest at `app/Modules/<Module>/Manifests/<Page>Resource.php`.
- The manifest is the module-local source of truth for:
    - route profile
    - route prefix
    - route name prefix
    - role scope
    - middleware
    - API defaults
    - table columns
    - mobile fields
    - form fields
    - realtime enablement
- When regenerating an existing CRUD resource, the generator should reuse manifest defaults instead of forcing the same options to be passed again.
