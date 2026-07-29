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
    - `resources/js/modules/<module>/contracts/dashboard-nav.ts`
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
- `app/Modules/<Module>/Data/<Module><Page>StoreData.php`
- `app/Modules/<Module>/Data/<Module><Page>ListItemData.php`
- `app/Modules/<Module>/Data/<Module><Page>PageData.php`
- `app/Modules/<Module>/Queries/<Model>Queries.php`
- `app/Modules/<Module>/Commands/<Model>Commands.php`
- `app/Modules/<Module>/Handlers/**` (`crud-api`)
- `app/Modules/<Module>/Manifests/<Page>Resource.php` (`crud`)
- `app/Modules/<Module>/Routes/web.php` (`crud`)
- `app/Modules/<Module>/Routes/api.php` (`api`)
- `app/Modules/<Module>/Routes/gates.php` (`crud`, role-restricted app routes)
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

It exports two contracts:

- **Symbol auto-import** (`autoImportDirs`, `autoImportImports`) - drives `unplugin-auto-import` in
  `vite.config.ts` and `vitest.config.ts`, and the restricted-import rules in `eslint.config.js`.
- **Component auto-registration** (`componentAutoImportOptions`, `inertiaComponentResolver`,
  `iconComponentPrefix`) - drives `unplugin-vue-components` in both `vite.config.ts` and
  `vitest.config.ts`, so a mounted component resolves `Ui*`, `Base*`, and module components in
  tests exactly as it does at runtime. Only stub children that need a live runtime dependency
  (for example `Link`, which needs the Inertia router).

### Generated declarations

Vite writes the following committed declaration files from this configuration:

- `resources/js/types/auto-imports.d.ts`
- `resources/js/types/components.d.ts`

Do not edit these files manually. After changing auto-import configuration or component files,
run the complete generated-artifact check:

```bash
composer generate
npm run build:ssr
```

CI verifies backend contracts immediately after `composer generate`, then verifies both backend
and frontend generated artifacts after the Vite client and SSR build.

### Import rules

`eslint.config.js` uses `@typescript-eslint/no-restricted-imports` with `allowTypeImports: true`:

- Runtime values from `@/composables/**`, `@/stores/**`, `@/lib/**`, `@/utils/**`,
  `@/modules/**/composables/**`, and `@/modules/**/helpers/**` must come from auto-import.
- `import type { ... }` from those paths stays legal, because auto-import only provides values.
- Files _inside_ those directories are exempt from auto-import restrictions: they wire up
  their own siblings with explicit imports instead of relying on auto-import resolving back
  into the directory being scanned. Feature-module boundaries still apply, including to
  module-local composables/helpers and tests.

Auto-imported symbols used only inside a `<template>` are not typed by `vue-tsc` (the generated
`declare module 'vue'` augmentation does not merge into `@vue/runtime-core`). Derive a
`computed` in `<script setup>` instead of calling an auto-imported helper directly in markup.
