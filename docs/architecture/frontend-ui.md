# Frontend UI, Theme, Forms, And Tables

Read before changing shared components, UI primitives, theme tokens or recipes, icons, accessibility behavior, form schemas, or server-driven tables.

## Component Layering

- `resources/js/components/ui/**` = low-level primitive wrappers (reka-ui, icons, etc.), built as shadcn-vue style components on top of Reka UI.
- Base UI component names use the `Ui*` prefix (for example `UiButton`, `UiInput`, `UiSelect`, `UiCard`, `UiDialog`). Use these existing primitives before building custom elements.
- `resources/js/components/base/**` = reusable app-level building blocks (`Base*`). Prefer composing `Base*` components rather than rebuilding common structures.
- `resources/js/modules/**` = feature-specific screens, dialogs, tables, and contracts.

## Theme Contracts

- `resources/css/theme.css` is the sole owner of raw visual values: semantic colors, light/dark mappings, status tones, radii, shadows, glass/overlay values, gradients, and reduced-motion defaults.
- `resources/js/lib/theme.ts` is the sole owner of reusable class recipes and typed style variants for shared controls, surfaces, dialogs, sheets, menus, inputs, tables, pagination, feedback, status, navigation, and animation.
- `appTheme` is auto-imported, so templates read it through a `const theme = appTheme` alias declared in `<script setup>` ([why](frontend.md#auto-import-and-component-registration)). UI primitives import the theme explicitly.
- `components.json` points the shadcn-vue CLI at `resources/css/theme.css` and declares no `iconLibrary`. Newly vendored primitives must be rewritten onto `appTheme` recipes and `~icons/*` before they are committed.
- `stubs/module-generation/frontend/**` emits real frontend source, so it follows every rule in this reference. Generated icons use `~icons/*` virtual imports.
- `resources/js/lib/__tests__/theme.test.ts` enforces the theme contracts across both `resources/js/**` and the stubs: no raw colors or palette utilities, no local `<style>` blocks, no runtime icon packages, no literal re-declaration of a canonical recipe, and no bare `appTheme.` inside a template.

## Primitive Prop Forwarding

- Forward props to a reka-ui primitive with `useForwardedProps` / `useForwardedPropsEmits` from `@/lib/forward-props`, narrowing with `reactiveOmit` where a prop is consumed locally. reka-ui already drops undefined values while forwarding but types the result with every key present, which `exactOptionalPropertyTypes` rejects; the adapters only restate that runtime contract in the type system, so they cost nothing at runtime. Never assert `as Partial<...>` or `as Record<string, unknown>`: both hide missing required props, and re-filtering an already-forwarded object duplicates work reka-ui did.
- `omitUndefinedProps` remains the tool for sanitising an object the component builds itself. The forwarding adapters read the calling component instance, so they only ever forward that component's own props.

## Icons

- Icons come only from Iconify through `unplugin-icons` using the `Icon*` component prefix. Use auto-resolved tags such as `<IconLucideChevronLeft />` in templates and virtual imports such as `~icons/lucide/chevron-left` when a component value is required in TypeScript. Application chrome uses the `lucide` collection; a multi-word collection needs the explicit `<Icon-<collection>:<name> />` form, as in `<Icon-icon-park-outline:system />`.
- Icon-bearing contracts use Vue's generic `Component` type so they remain collection-agnostic.

## Accessibility

Shared UI primitives must include baseline accessibility: visible focus states, meaningful `aria-*` labels for icon-only controls, keyboard-operable interactions, and color-contrast-safe active/focus states. Generated element ids follow the [deterministic markup](frontend-rendering.md#deterministic-markup) rule.

## Forms

- Form schemas belong in `resources/js/modules/**/forms/*-form-schema.ts`, never inline in pages/components.
- Current shared form contracts live in:
    - `resources/js/lib/forms.ts`
    - `resources/js/types/base-ui.ts`
    - `resources/js/components/base/forms/**`
- Prefer `FormValuesFromData<...>` from `resources/js/lib/forms.ts` when form values come from backend DTOs.
- Prefer `defineFormContract(...)` and `defineFormFields(...)` for all form schemas. Both live in `resources/js/lib/forms.ts` and are auto-imported. `resources/js/types/base-ui.ts` keeps the field/schema _types_ only.
- Prefer `useSchemaResourceForm(...)` over hand-wired form state in page/components when the form matches the shared resource pattern, and render it with `BaseFormsBaseFormRenderer` directly (`:model="form"`). Reserve `useResourceForm()` for non-schema actions with no typed field contract, such as empty confirm or resend forms.
- Schema form checkboxes own their inline label; `BaseFieldShell` hides its duplicate label for those fields. Select triggers receive the field ID so the shell label targets the focusable control.
- Schema controls associate descriptions and errors through `aria-describedby` and expose invalid/required state. Processing disables the form's fields and prevents another submit event. Read-only text remains selectable; choice/file controls disable interaction. Every choice variant honors disabled options.
- Schema form IDs use a per-instance Vue `useId()` prefix so repeated field names retain unique, SSR-stable label and feedback targets.

## Server Tables

- Server-driven listing pages must use the shared table stack:
    - `resources/js/composables/useServerDataTable.ts`
    - `resources/js/components/base/table/**`
- Initial query state must be derived via `resolveServerTableInitialQuery(...)`.
- That derivation reads the request URL from the shared `location` prop, which `HandleInertiaRequests::share()` sets from `$request->fullUrl()`. It must stay absolute and keep the query string: Inertia's own `page.url` is relative, and `$request->url()` would drop the query and silently reset a shared or bookmarked listing URL to the default sort.
- Pass a reactive `initialQuery` (getter or computed ref) to `useServerDataTable` so preserved-state redirects synchronize pagination, search, and sort without another visit. Users derives it from the current location and pagination props; generated pages derive it from pagination props. Pending searches that already match server state do not issue a stale request.
- Keep row typing strict; never cast rows.
- Paginated page DTOs share one envelope; see [generator behavior](module-generator.md#generator-behavior).
- Current standard server-table query contract:
    - `page: number`
    - `perPage: number`
    - `search?: string`
    - `sortBy?: string`
    - `sortDirection?: 'asc' | 'desc'`
- Current allowed users `sortBy` values:
    - `name`
    - `email`
    - `role`
    - `created_at`
