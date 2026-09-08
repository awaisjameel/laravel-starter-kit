# Starter kit audit

Audit date: 2026-09-09. This records the repository repairs and the validation boundary; it is not a security certification or a promise that every future workload needs the same architecture.

## Scope

Reviewed the dependency manifests and locks, Laravel bootstrap and module discovery, authentication and authorization, DTO/request/response flows, user mutations and realtime dispatch, module generation, frontend entries and SSR isolation, API composables, UI typing and pagination, theme and auto-import enforcement, process configuration, CI, and contributor guidance. Existing modular ownership and backend-generated contracts remain canonical.

The locked Composer and npm graphs reported no known security advisories during this audit. No dependency upgrade or additional runtime package was necessary for these repairs. This does not establish that all transitive dependency source code is free of defects. The existing Wayfinder portability patch, TypeScript bridge pin, reviewed install scripts, and platform bindings remain subject to their documented removal and upgrade checks.

## Repairs

| Area                           | Finding and resulting behavior                                                                                                                                                                                                                                                                      |
| ------------------------------ | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Session API requests           | The fetch client used a CSRF meta tag the app did not render. Mutations now read the current decoded `XSRF-TOKEN` cookie into `X-XSRF-TOKEN`. Automatic CSRF and socket headers remain same-origin. Added query values preserve existing parameters and fragments.                                  |
| Email verification             | Admin email changes could preserve verification of the previous address. The user model now owns verification invalidation for every model-save path; the duplicate profile-controller check is removed.                                                                                            |
| Mutation events                | Deletion events were dispatched before deletion, and transaction rollback could leave side effects. Deletion completes first; user management events dispatch after commit. Failure and rollback regressions cover the boundary.                                                                    |
| Sensitive authentication       | Password confirmation now uses the shared sensitive-authentication limiter, with exhaustion and recovery coverage.                                                                                                                                                                                  |
| Process lifecycle              | Two scheduler processes could execute the same scheduled work. The configuration starts one scheduler and lets long-running services restart after normal deployment exits. Multi-host singleton scheduling still needs deployment-specific coordination.                                           |
| UI type coverage               | Vendored UI primitives were excluded from checking and linting. They now participate in both. Required props remain typed while undefined optional props are removed through the existing shared helper; broad record casts are removed.                                                            |
| Table pagination               | A mounted regression reproduced two page-change events for one click. The Reka pagination root now owns the single emission. User queries add an ID tie-breaker for stable pagination.                                                                                                              |
| Scaffold correctness           | CRUD model generation omitted a required token; page-only forms reused a CRUD DTO template; plain API resource mapping used a static closure with `$this`. These paths now render valid code. Unknown tokens and invalid PHP/JavaScript identifiers fail early.                                     |
| Scaffold pagination            | Generated listings now accept validated backend-owned pagination DTOs, return typed metadata, expose working frontend controls, and use deterministic ordering. Actual generated web/API routes are exercised across resource/plain and standalone/combined variants.                               |
| API state                      | Custom error shapes require a mapper. Concurrent mutations retain pending state until all work finishes; the latest invocation owns displayed results. Reset supersedes pending display updates. Callback failures cannot turn a successful write into rollback handling, and settlement runs once. |
| Identity and SSR               | Client query caches clear when authenticated identity changes. The watcher starts after the child Inertia app initializes page state. Existing SSR protections keep caches and request state out of shared server globals.                                                                          |
| Enforcement and agent guidance | ESLint fails on warnings in both modes. CI audits both locked dependency graphs. `AGENTS.md` includes a task entry map and updated invariants; `CLAUDE.md` imports the canonical guide. Related frontend and generator docs are aligned.                                                            |

The CSRF header follows [Inertia's Laravel CSRF guidance](https://inertiajs.com/docs/v3/security/csrf-protection). Transactional event delivery uses [Laravel's after-commit event contract](https://laravel.com/framework/docs/events).

## Verification

The completion checks are:

```bash
composer validate --strict
composer audit --locked
npm audit
composer generate-and-cleanup
composer qa:check
composer test
npm run test:unit
npm run build:ssr
git diff --check
```

These include Rector, Pint, PHPStan level 9 with strict/deprecation rules, Prettier, ESLint with zero warnings, Vue/TypeScript checking, backend and frontend regressions, and production client/SSR compilation. Generated contract contents are also compared before and after regeneration/build to detect drift.

A temporary `AuditVerification` CRUD/API module was generated in the real application, passed the generation/cleanup pipeline, its generated backend tests, and client/SSR builds. The fixture source and generated references were subsequently removed. Permanent generator regression tests remain in the repository.

Browser smoke checks used production-built client assets on a local server with SSR disabled. Landing, login, registration, and password-reset pages rendered and navigated without browser console warnings or errors. These checks did not submit authentication forms or create accounts.

## Staged-change follow-up

The staged-change review traced the 85-file patch through existing consumers and found three additional integration gaps:

- Resource API pagination links discarded `perPage`. Both generated resource controllers now preserve the query string, following the existing Users API pattern. Following the returned next-page URL is covered by a regression for both standalone and combined APIs.
- Preserved-state mutation redirects could return page 1 while `useServerDataTable` retained page 2 and the previous page size. The shared composable now accepts a reactive pagination getter; Users and generated CRUD pages supply their backend metadata. A regression verifies synchronization without an extra request and verifies that the next page remains reachable.
- Identifier-prefix validation accepted PHP reserved model names. CRUD/API inputs now reject reserved keywords and type names before writing files, with regressions for `Class`, `String`, and `Match`.

Plain API stubs now serialize metadata through the same `PaginationData` used by generated page DTOs. The sidebar's remaining `Partial` prop assertion was replaced with typed undefined omission, and a stale theme-test comment about excluded UI typechecking was corrected.

A temporary `StagedAuditProbe` CRUD/API module passed `composer generate-and-cleanup`, the full backend suite (144 tests), the full frontend suite (84 tests), and `npm run build:ssr`. Its source, migration, tests, and generated helpers were removed afterward. No migration was applied to the application's database. Dependency validation and both locked-graph audits passed. This follow-up did not repeat the earlier browser smoke checks.

## Validation limits

Local checks ran on Windows with the repository's test database configuration. Linux CI, real PM2 process restarts, production SSR hydration, authenticated browser workflows, live Reverb delivery, external mail, proxy/TLS configuration, and multi-host or load behavior were not exercised against a deployed environment. The suite and builds verify the corresponding repository contracts where tests exist; they do not replace those operational checks.

Scale-dependent infrastructure choices should follow measured workload requirements. Keep backend contracts canonical, exercise generated modules through their actual transports, and run the documented gates whenever those contracts or dependency pins change.
