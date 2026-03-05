# How To Add A New Module Page

Use this checklist when adding a new frontend page to keep it reusable, typed, and consistent with project DX standards.

## 0. Prefer scaffold automation

For a brand-new module:

```bash
php artisan generate:module <ModuleName> --scaffold=crud --page=<PageName>
```

For an existing module:

```bash
php artisan generate:module <ModuleName> --extend --scaffold=page --page=<PageName>
```

## 1. Backend entrypoint

1. Add/extend controller in `app/Modules/<Module>/Http/Controllers`.
2. Add route in module route file and ensure it is aggregated by `routes/web.php` or `routes/api.php`.
3. Return Inertia page from controller using module page path, for example:
   - `modules/<module>/pages/<Page>.vue`

## 2. Frontend page file

1. Create page at `resources/js/modules/<module>/pages/<Page>.vue`.
2. Use `<script setup lang="ts">`.
3. Use typed props (`defineProps<...>()`) and generated route helpers (`route(...)` + wayfinder actions).
4. Use shared layout (`AppLayout`, `AuthLayout`, etc.) and shared primitives from `components/base/**`.

## 3. Forms (if page has form inputs)

1. Create schema file under `resources/js/modules/<module>/forms/*-form-schema.ts`.
2. Define typed form values interface and fields via `defineFormFields`.
3. Use `useSchemaResourceForm<TForm>()` in page/component so defaults + fields come from the schema contract.
4. Render with `BaseFormsBaseFormRenderer` directly (`:model="form"`).
5. Do not use `as unknown as Record<string, unknown>`.
6. Reserve `useResourceForm()` for non-schema actions with no typed field contract (for example, empty confirm/re-send forms).

## 4. Server tables (if page lists server data)

1. Use `useServerDataTable`.
2. Build initial query with `resolveServerTableInitialQuery`.
3. Use shared table primitives from `components/base/table/**`.
4. Keep row typing strict (avoid row casts).

## 5. Navigation (if page is navigable)

1. Add nav item(s) through `resources/js/config/navigation.ts`.
2. Consume nav builders from layouts/composables; do not duplicate nav arrays in page files.

## 6. Tests and quality gate

Run after changes:

```bash
npm run test:unit
composer generate-and-cleanup
php artisan test
```

## 7. Generated files rule

Do not hand-edit generated artifacts:

- `resources/js/actions/**`
- `resources/js/routes/**`
- `resources/js/wayfinder/index.ts`
- `resources/js/types/app-data.ts`
- `resources/js/types/auto-imports.d.ts`
- `resources/js/types/components.d.ts`

## 8. Architecture lint and accessibility contract

1. Do not inline form schema arrays in pages/components (`const fields = [...]`); define schemas in `modules/**/forms`.
2. Do not duplicate navigation arrays in pages/layouts; use `resources/js/config/navigation.ts`.
3. Ensure accessibility baseline for shared/feature UI:
   - visible focus states
   - `aria-label` for icon-only controls
   - keyboard-operable controls
   - active/focus colors with readable contrast
