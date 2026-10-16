# Stack, Versions, And Dependencies

Read before adding, removing, or upgrading a Composer or npm dependency, changing a Composer patch, touching `optionalDependencies`, changing the TypeScript bridge, ESLint composition, or engine requirements, or setting up an editor.

## Current Stack

- PHP: `^8.4`
- Laravel: `^13.36`
- Inertia Laravel: `^3.5`
- Inertia client (`@inertiajs/vue3`, `@inertiajs/vite`): `^3.9`
- Reverb: `^1.12`
- Sanctum: `^4.3.3`
- Wayfinder: `^0.1.21` ([sole route surface](frontend-data.md#route-helpers))
- Spatie Laravel Data: `^4.23`
- Spatie TypeScript Transformer: `^3.3`
- Pest: `^5.3` with Laravel `^5.0` and PHPStan `^5.2` plugins (PHPUnit 13 engine)
- PHPStan/Larastan: `^2.3` / `^3.13` at level 9 with official strict and deprecation rules
- Rector: `^2.7`
- Pint: `^1.32`
- Laravel Boost: `^2.10`
- Vue: `^3.5.44`
- VueUse: `^15.0`
- Reka UI: `^2.11`
- TypeScript checker: `7.0.2` through `typescript-native-bridge` (`6.0.3-bridge.18.tsgo.7.0.2`); see [TypeScript bridge](#typescript-bridge)
- Vitest: `^5.0` with jsdom `^30.1`
- ESLint: `^10.12`
- Prettier: `^3.9`
- Vite: `^8.3` (Rolldown bundler)
- `laravel-vite-plugin`: `^3.2`
- Tailwind CSS: `^4.3`
- Pinia: `^4.0`
- Icons: Iconify collections compiled by `unplugin-icons` `^24.0` ([icons](frontend-ui.md#icons))
- Node: `>=24.15.0`
- npm: `>=11.2.1`

## Package Management

- `.npmrc` enables `engine-strict` so installs reject incompatible dependency runtimes. Node 24.15+ is required by jsdom 30; CI uses the latest Node 24 release.
- npm's `allowScripts` has no approved dependency scripts. VueUse 15 no longer needs the `vue-demi` adapter; review any new install scripts individually.
- Published Sail Docker contexts are limited to `docker/8.4` and `docker/8.5`, matching the Composer PHP constraint.

## Version Constraints

### Composer Patches

- Wayfinder 0.1.21 mixes platform line separators with LF Blade templates, which produces different indentation and blank lines on Windows and Linux. `cweagans/composer-patches` applies `patches/wayfinder-portable-newlines.patch` during installation so Artisan, Composer, and Vite's automatic regeneration all emit identical LF helpers. Do not edit vendor files or format generated helpers separately, and keep the CI drift check strict. Remove the patch after an upstream release passes the portability regression tests on Windows and Linux.
- Wayfinder 0.1.21's method union predates Laravel 13's `QUERY` verb. `patches/wayfinder-query-method.patch` adds it to the generated type contract; its regression covers route, action, and form helpers. Remove this patch when upstream supports `QUERY` and the query-route regression and Vue typecheck pass.
- `composer install` applies both patches to a fresh installation and requires Git. An existing vendor directory that predates a patch needs `composer patches-repatch` once.
- After changing a patch, run `composer patches-relock`, `composer patches-repatch`, and the generation/quality gates, then commit the patch, `patches.lock.json`, and the regenerated helpers together. CI vendor caches include the patch lock and patch files so an older unpatched vendor directory cannot be restored.

### TypeScript Bridge

- `npm run typecheck` runs `vue-tsc --noEmit` with the TypeScript 7.0.2 native checker through [TypeScript Native Bridge](https://github.com/johnsoncodehk/typescript-native-bridge). `typescript` is an exact npm alias to `typescript-native-bridge`, with `overrides.typescript = "$typescript"` so Vue, ESLint, and Prettier share the TypeScript 7 checker and classic API adapter. The version's `6.0.3` prefix identifies the classic JavaScript API facade; `tsgo.7.0.2` identifies the checker. The bridge prints `TNB ACTIVE` when its checker starts; the message is informational.
- The bridge preserves the API used by Vue's virtual files, ESLint, and Prettier's import organizer, which [Microsoft's stock TypeScript 7 package](https://devblogs.microsoft.com/typescript/announcing-typescript-7-0/) does not provide. Replace it with stock TypeScript only when all three consumers support it, then remove the npm override and revalidate the complete pipeline.
- Keep the bridge exactly pinned, commit the regenerated lockfile, and revalidate compiler diagnostics, formatting, lint, client/SSR builds, and clean `npm ci` when changing it. `resources/js/types/__tests__/compiler.test.ts` runs the real Vue checker against temporary fixtures in `npm run test:unit`, checking TypeScript 7 Unicode inference and rejection of invalid Vue props, template methods, and nullable backend fields.
- The bridge ships native binaries for Windows, macOS, and glibc Linux (2.31+), including the Ubuntu 24.04 Sail images and Ubuntu CI. Alpine/musl is unsupported; run frontend tooling in a glibc build stage. Platform binaries come from its locked optional dependencies, so never omit optional dependencies during installation.

### Editor Setup

- In VS Code, enable Vue - Official and choose **TypeScript: Select TypeScript Version → Use Workspace Version**. The committed `.vscode/settings.json` points the SDK at `node_modules/typescript/lib`; editors using the older setting names can set `typescript.tsdk` to that path and `typescript.enablePromptUseWorkspaceTsdk` to `true`.
- Keep the classic language service for Vue plugin support. Disable the separate TypeScript 7 language server if it takes over the workspace, since it does not support the Vue plugin model.

### Native Bindings

- `optionalDependencies` pin the Linux x64 native binaries used by CI/Docker. Vite 8 bundles with Rolldown, so the binding is `@rolldown/binding-linux-x64-gnu` (not `@rollup/rollup-*`).
- Those three entries must use exact versions that match the resolved core packages (`rolldown`, `lightningcss`, `@tailwindcss/oxide`). A caret range can hoist a newer binding than the core package expects and break the Linux build. Re-check them after any Vite or Tailwind bump.
- Vite 8.3 uses Lightning CSS 1.33, while Tailwind 4.3.3 pins 1.32. Keep each core's matching binding in the lockfile; npm may install Tailwind's binding beneath its older core.

### Other Pins

- `concurrently` 10.0.6 pins the fixed `shell-quote` 1.12.0, so no security override is needed.
- `fontaine` stays on `^0.8.2`, the latest release supported by `laravel-vite-plugin` 3.2's optional `^0.8.0` peer. Upgrade it to 1.x when the plugin supports that peer; keep metric-matched self-hosted font fallbacks and strict peer checks.
- ESLint composes `typescript-eslint`'s recommended configs with `eslint-plugin-vue` and `vue-eslint-parser` directly, including TypeScript expressions in templates. Preserve the Vue script/template parser, `vue/block-lang`, and every [module/import guard](frontend.md#eslint-guards); Vue typechecking remains the separate `vue-tsc` gate. `@vue/eslint-config-typescript` was removed because its `fast-glob` dependency pulls in unpatched vulnerable `braces`; no project file scan is needed for this non-type-aware lint configuration.

## Dependency Addition Policy

- Prefer the existing Laravel, Vue, Inertia, Spatie Data, and shared project abstractions before adding new dependencies.
- New packages require strong justification: a demonstrated requirement that no existing capability satisfies, without meaningful overlap with tools already in the stack.
- Prefer native framework capabilities when they are already sufficient and project-consistent.
- Avoid introducing dependencies that make generated contracts, SSR, strict typing, or CI harder to maintain.
