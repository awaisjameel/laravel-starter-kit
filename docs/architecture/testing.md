# Testing

Read before writing or moving tests, changing test configuration (`tests/Pest.php`, `phpunit.xml`, `phpstan.neon.dist`, `vitest.config.ts`), or changing test scripts. What every change must cover is in the [change-impact checklist](../../AGENTS.md#change-impact-checklist).

## Backend (Pest)

- Pest 5 is the canonical backend test runner. Write backend tests, including those emitted by the module generator's stubs, in native Pest functional syntax.
- `tests/Pest.php` binds both `Feature` and `Unit` suites to `Tests\TestCase`.
- Pest's compact printer is configured centrally in `tests/Pest.php`; do not duplicate `--compact` across scripts.
- `composer test` runs the suite in parallel, locally and in CI. `composer test:serial` is the single-process escape hatch for debugging output, and `composer test:fast` adds test impact analysis when `pcov` or Xdebug is installed.
- Each test script is a single command, so extra arguments reach Pest directly: `composer test -- --filter=Registration`.
- `phpunit.xml` redirects `APP_CONFIG_CACHE`, `APP_EVENTS_CACHE`, and `APP_ROUTES_CACHE` to paths that never exist, so a locally cached boot manifest can never override the declared test environment. Do not reintroduce a cache-clearing step in the test scripts.
- PHPUnit is configured to fail on every reported issue and to detect unexpected global-state changes or test output.
- PHPStan analyzes native Pest closures through `pestphp/pest-plugin-phpstan`; keep its extension registered in `phpstan.neon.dist`.
- PHPStan runs at level 9 with `phpstan/phpstan-strict-rules` and `phpstan/phpstan-deprecation-rules`, with no baseline or ignored-error escape hatch.
- The strict rule forbidding dynamic calls to static methods is disabled because Pest/PHPUnit assertions and Laravel fluent builders intentionally expose instance syntax over methods declared static. Keep all other strict rules enabled.
- Apply `RefreshDatabase` only in files that need database isolation; do not make it global.
- Reusable backend test setup and fixture behavior belongs under `tests/Support/**`, not in duplicated file-local functions.
- Current backend test areas already include:
    - auth flows
    - settings flows
    - dashboard and marketing rendering
    - users web management
    - users realtime
    - API v1 endpoints
    - security headers
    - module registry / route / gate / channel / listener / provider discovery
    - generator behavior
    - strict-types and security architecture rules (`tests/Unit/ArchitectureTest.php`)

## Frontend (Vitest)

- Frontend logic tests live as `resources/js/**/*.test.ts` and run under Vitest with `jsdom` through `npm run test:unit`.
- `vitest.config.ts` mirrors the runtime auto-import and component registration; see [frontend](frontend.md#auto-import-and-component-registration) for what may be stubbed.
- The compiler regression test is described under [TypeScript bridge](stack.md#typescript-bridge).

## Placement

- Backend transport behavior belongs in `tests/Feature/**`.
- Backend pure/domain orchestration behavior belongs in `tests/Unit/**`.
- Module-specific backend tests should stay grouped under their module namespace when practical.
- Frontend composable, contract, and utility tests should live close to the code under `resources/js/**/__tests__/**` or `resources/js/**/*.test.ts`.
- Generator and infrastructure behavior should be covered near their owning area, not mixed into unrelated feature suites.
