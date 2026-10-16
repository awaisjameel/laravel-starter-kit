# Quality Gates, Scripts, And CI

Read before changing Composer or npm scripts, tool configuration (Rector, Pint, PHPStan, Prettier, ESLint, `vue-tsc`), tool caches, or CI workflows. Which gates a task must pass is defined in [AGENTS.md](../../AGENTS.md#quality-gate); this reference explains what each command runs.

## Command Composition

- `composer run-script --list` prints a description for every script.
- `composer generate` composition is owned by [contracts](contracts.md#generation-command).
- `composer generate-and-cleanup` currently runs:
    - route/type/helper generation
    - `composer cleanup`
- `composer cleanup` runs two pipelines concurrently and groups their output:
    - `composer cleanup:php`
    - `npm run cleanup`
- `composer cleanup:php` currently runs, in order:
    - `vendor/bin/rector process`
    - `vendor/bin/pint`
    - `vendor/bin/phpstan analyse --ansi`
- PHP quality tools have canonical Composer entry points:
    - `composer refactor` / `composer refactor:check`
    - `composer format` / `composer format:check`
    - `composer analyse`
- Rector runs before Pint so AST rewrites always receive the final canonical formatting pass.
- `npm run cleanup` currently runs:
    - `npm run format`
    - `npm run lint`
    - `npm run typecheck`
- `composer qa:check` runs `composer qa:php`, `npm run format:check`, `npm run lint:check`, and `npm run typecheck` concurrently. It must never rewrite source files.
- `composer qa:php` runs Rector in dry-run mode, Pint in test mode, then PHPStan.
- Test commands are described in [testing](testing.md).

## Script Performance Rules

- The PHP pipelines stay ordered because Rector, Pint, and PHPStan share the same files; only independent pipelines are parallelized, so the slowest pipeline sets the wall time instead of the sum of all tools.
- Aggregate scripts orchestrate through `concurrently` and reference the canonical single-tool scripts. Never inline a tool invocation that already has an entry point.
- Every tool that supports a result cache must keep one: Rector and PHPStan under `storage/framework/cache/**`, Prettier, ESLint, and `vue-tsc` under `node_modules/.cache/**`. Frontend caches use the content strategy so they survive branch switches and CI checkouts, and CI restores the same caches.
- Pint runs single-process. `--parallel` spawns a worker per core and is slower than a single process at this repository's file count; only revisit it if the PHP file count grows by an order of magnitude.

## CI Workflows

- The shared setup action (`.github/actions/setup-project`) uses `actions/cache@v6` with the Node 24 action runtime; dependency and patch locks are part of the cache identity. It runs `composer validate --strict`, installs both locked dependency graphs (`composer install`, `npm ci`), prepares `.env`, and runs `composer generate`.
- The lint workflow runs `composer qa:check`. Both mutating and non-mutating ESLint commands fail on warnings.
- The test workflow builds the client and SSR assets, audits both locked graphs (`composer audit --locked`, `npm audit`), then fails if generated types, auto-import/component declarations, routes, actions, or the Wayfinder runtime have tracked changes or untracked files. It then runs `npm run test:unit` and `composer test`. Regenerate and commit the complete contract surface together.
- A second test job runs `composer qa:generated` (`scripts/verify-generated-module.sh`). It snapshots tracked changes and untracked source into an isolated checkout, installs both locked dependency graphs, scaffolds protected and public modules, builds client/SSR assets, and runs `composer generate-and-cleanup`, `composer qa:check`, both suites, and whitespace verification including new files. A generated public-page SSR regression uses the real layout and a guest Inertia page context. The source checkout, index, database, and build outputs remain untouched even on failure; only the temporary checkout is deleted. Name-dependent formatting is handled by the canonical cleanup inside the gate. This proves generated modules compile and execute in the application, beyond the generator's template assertions.
