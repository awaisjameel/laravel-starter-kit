# Frontend Automation

## Module and page scaffolding

Use the Artisan generator to scaffold a module with backend + frontend contracts:

```bash
php artisan generate:module <ModuleName> --scaffold=crud --page=<PageName>
```

Example:

```bash
php artisan generate:module Billing --scaffold=crud --page=Index
```

Available scaffold targets:

- `page`: frontend form/page/test contracts only
- `crud`: web CRUD scaffold (+ optional frontend page contracts)
- `api`: API CRUD scaffold
- `crud-api`: both web CRUD and API CRUD

For existing modules, use extend mode with page scaffold to add frontend contracts only:

```bash
php artisan generate:module Users --extend --scaffold=page --page=InviteUser
```

In interactive shells, the command first asks scaffold/profile questions, then asks for confirmation per generated file.
Use `--no-file-prompts` to skip per-file confirmations and generate all planned files directly.

Generated frontend files:

- `--scaffold=page`:
  - `resources/js/modules/<module>/forms/<page>-form-schema.ts`
  - `resources/js/modules/<module>/pages/<Page>.vue`
  - `resources/js/modules/<module>/pages/__tests__/<Page>.test.ts`
- `--scaffold=crud` (when page generation is enabled):
  - `resources/js/modules/<module>/contracts/<page>-crud.ts`
  - `resources/js/modules/<module>/forms/<page>-form-schema.ts`
  - `resources/js/modules/<module>/components/Table.vue`
  - `resources/js/modules/<module>/components/<Page>FormDialog.vue`
  - `resources/js/modules/<module>/components/<Page>DeleteDialog.vue`
  - `resources/js/modules/<module>/components/<Page>DetailsDialog.vue`
  - `resources/js/modules/<module>/pages/<Page>.vue`
  - `resources/js/modules/<module>/pages/__tests__/<Page>.test.ts`

Generated backend additions (fresh module mode):

- `app/Modules/<Module>/Http/Controllers/<Page>Controller.php` (`crud`)
- `app/Modules/<Module>/Http/Controllers/<Page>ApiController.php` (`api`)
- `app/Modules/<Module>/Http/Requests/<Page>StoreRequest.php`
- `app/Modules/<Module>/Http/Requests/<Page>UpdateRequest.php`
- `app/Modules/<Module>/Data/<Page>StoreData.php`
- `app/Modules/<Module>/Services/<Page>Service.php`
- `app/Modules/<Module>/Routes/web.php` (`crud`)
- `app/Modules/<Module>/Routes/api.php` (`api`)
- `app/Modules/<Module>/Http/Resources/<Page>Resource.php` (`api`, optional)
- `app/Models/<Model>.php`
- `database/migrations/*_create_<table>_table.php`
- `tests/Feature/<Module>/<Page>PageTest.php` (`crud`)
- `tests/Feature/<Module>/<Page>ApiTest.php` (`api`)

## Auto-import source of truth

`frontend-auto-import.config.mjs` is the canonical definition for auto-import symbols and directories.

The following files consume it and must stay aligned:

- `vite.config.ts`
- `vitest.config.ts`
- `eslint.config.js`
