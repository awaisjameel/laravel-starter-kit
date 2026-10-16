# Frontend Architecture

Read before adding or moving frontend source, changing TypeScript declarations, auto-import or component registration, ESLint import rules, navigation, or toasts.

## Source Layout

- Feature pages live in `resources/js/modules/**/pages`.
- Feature forms live in `resources/js/modules/**/forms`.
- Feature-specific components live in `resources/js/modules/**/components`.
- Feature-specific contracts live in `resources/js/modules/**/contracts`.
- Feature-specific composables live in `resources/js/modules/**/composables`.
- Feature-specific helpers live in `resources/js/modules/**/helpers`.
- Shared layers:
    - `resources/js/components/**`
    - `resources/js/layouts/**`
    - `resources/js/composables/**`
    - `resources/js/config/**`
    - `resources/js/lib/**`
    - `resources/js/utils/**`
- Do not place feature-specific UI in `resources/js/components/**`. If code is needed across modules, promote it to a shared layer instead of crossing module boundaries.
- `resources/js/types/index.d.ts` is for app-shell/shared UI types, not duplicated backend domain contracts.

## TypeScript Declarations

- Use `<script setup lang="ts">`.
- Shared page props are typed through Inertia v3's `InertiaConfig.sharedPageProps` augmentation in `resources/js/types/globals.d.ts`, bound to the generated `SharedPageData`. `usePage()`, `$page`, and `createInertiaApp` are therefore typed without a type argument; do not cast individual shared props.
- `globals.d.ts` also declares the `VITE_*` variables the frontend reads, and enables Vite's `strictImportMetaEnv`, so reading an undeclared `import.meta.env` key is a type error instead of `any`. Declare a new client variable there when adding it to `.env.example`.

## Auto-Import And Component Registration

- Canonical auto-import config: `frontend-auto-import.config.mjs`
- The following files must stay aligned with that config:
    - `vite.config.ts`
    - `vitest.config.ts`
    - `eslint.config.js`
- Auto-imported directories currently include:
    - `resources/js/composables/**`
    - `resources/js/stores/**`
    - `resources/js/lib/**`
    - `resources/js/utils/**`
    - `resources/js/modules/**/composables/**`
    - `resources/js/modules/**/helpers/**`
- Module-local `forms/**` and `contracts/**` are not auto-imported.
- Inertia owns routing; do not enable the Vue Router auto-import preset without a Vue Router runtime. Type-only imports such as `Method` remain explicit.
- `frontend-auto-import.config.mjs` owns two contracts:
    - symbol auto-import (`autoImportDirs`, `autoImportImports`, restricted paths/patterns), which drives `unplugin-auto-import` and the ESLint restricted-import rules
    - component auto-registration (`componentAutoImportOptions`, `inertiaComponentResolver`, `iconComponentPrefix`)
- Vue components are auto-registered from:
    - `resources/js/components`
    - `resources/js/layouts`
    - `resources/js/modules`
- `vitest.config.ts` installs the same `unplugin-auto-import`, `unplugin-vue-components`, and `unplugin-icons` setup as `vite.config.ts`, so a mounted component resolves auto-imported symbols and `Ui*`, `Base*`, and module components exactly as it does at runtime. Both spread `autoImportOptions` and `componentAutoImportOptions` from `frontend-auto-import.config.mjs`; do not inline either option set. Only stub children that need a live runtime dependency, such as `Link`, which needs the Inertia router.
- Component auto-registration only rewrites compiled SFC templates. A runtime `template` string in a spec still needs an explicit component reference.
- Auto-imported symbols used only inside a `<template>` are not typed by `vue-tsc`, because the generated `declare module 'vue'` augmentation does not merge into `@vue/runtime-core`. Derive a `computed` (or alias the symbol) in `<script setup>` rather than using an auto-imported symbol directly in markup.
- Module Vue components are namespace-registered; use tags like `<UsersTable />` and `<UsersDeleteUserDialog />` instead of manual imports.
- `Link` and `Head` are resolver-provided; do not manually import them.

## ESLint Guards

- The ESLint config currently enforces:
    - no direct imports of auto-imported composables/stores/libs/utils/helpers/components
    - no cross-module imports between feature modules
    - no inline `fields = [...]` form arrays in pages/components
    - no duplicated navigation arrays in pages/layouts/composables
    - no `fetch(...)` calls inside feature page files
    - no `as unknown as Record<string, unknown>`
    - no explicit any type (`@typescript-eslint/no-explicit-any`)
- Restricted imports use `@typescript-eslint/no-restricted-imports` with `allowTypeImports: true`. Auto-import only provides runtime values, so `import type { ... }` from a restricted path stays legal everywhere.
- Three scopes are deliberately exempt from the auto-import restriction:
    - files inside the auto-imported directories themselves (`autoImportSourceGlobs`), which wire up their own siblings with explicit imports instead of relying on auto-import resolving back into the directory being scanned
    - test files, which must be able to name real components and module namespaces for mounting and spying
    - UI primitives, which keep explicit imports because the shadcn-vue CLI regenerates them, while receiving all other lint and type checks
- Feature-module source and test files still enforce the cross-module boundary rule.
- How the ESLint configuration itself is composed is recorded in [stack](stack.md#other-pins).

## Navigation And Breadcrumbs

- Shared navigation config: `resources/js/config/navigation.ts`
- Shared breadcrumb builders: `resources/js/config/breadcrumbs.ts`
- Active-state resolution lives in `resources/js/composables/useNavigation.ts`
- Dashboard CRUD navigation discovery lives in `resources/js/config/dashboard-crud-navigation.ts`
- Generated CRUD dashboard navigation contracts live at `resources/js/modules/<module>/contracts/dashboard-nav.ts`
- Do not duplicate navigation or breadcrumb arrays inline in pages/components/layouts.

## Notifications

- Global toasts are handled via `useToast` + `AppToaster`.
- Inertia flash props (`message`, `error`, `status`) are bridged to toasts through `useFlashToasts`.
- Toasts are client-only: `useToast` drops them during SSR and `AppToaster` renders none until it is mounted, which keeps the server output and the first client render identical. Do not remove either guard without moving the toast state out of module scope.
- Use inline messages only for persistent instructional content that should not be transient.
