# AGENTS.md

Modular Laravel 13 + Inertia 3 + Vue 3 starter kit with backend-owned contracts, strict typing end to end, SSR, realtime support, and generator-backed conventions. The backend is the source of truth for DTOs, enums, route/action helpers, and realtime contracts.

This file is always loaded and holds the rules for every task. Architecture detail lives in single-owner references, loaded on demand: before editing, read the reference for every area the task touches and trace the implementations it names. References never restate this file, and this file never restates them.

## Start Here

| Task                                  | Read first                                                                                                                                            | First implementation to inspect                                                           | Verification                                                         |
| ------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------- | ----------------------------------------------------------------------------------------- | -------------------------------------------------------------------- |
| CRUD, requests, authorization         | [backend](docs/architecture/backend.md), [contracts](docs/architecture/contracts.md), [routes and security](docs/architecture/routes-and-security.md) | `app/Modules/Users`, `tests/Feature/Users`, `tests/Feature/Api`                           | PHP suite, generation                                                |
| Forms, tables, themes                 | [frontend](docs/architecture/frontend.md), [frontend UI](docs/architecture/frontend-ui.md)                                                            | `resources/js/components/base`, `resources/js/lib`, `resources/js/modules/users`          | Vue typecheck, Vitest, client/SSR build                              |
| API state, caching, route helpers     | [frontend data](docs/architecture/frontend-data.md)                                                                                                   | `resources/js/composables/useApiQuery.ts`, `resources/js/composables/useApiClient.ts`     | Vitest, Vue typecheck                                                |
| App entry, SSR, hydration, CSP, fonts | [frontend rendering](docs/architecture/frontend-rendering.md), [runtime](docs/architecture/runtime.md)                                                | `resources/js/app.ts`, `resources/views/app.blade.php`, `app/Http/Middleware`             | Client/SSR build, security header tests, rendered page when relevant |
| Realtime and mutations                | [realtime](docs/architecture/realtime.md)                                                                                                             | `app/Modules/Shared/Realtime`, `app/Modules/Users/Listeners`, `resources/js/lib/realtime` | Realtime PHP/Vitest suites; live Reverb when relevant                |
| New page, discovery, or scaffolding   | [module generator](docs/architecture/module-generator.md), [backend](docs/architecture/backend.md#module-discovery)                                   | `app/Modules/Shared/Support`, `stubs/module-generation`                                   | Generator tests plus `composer qa:generated`                         |
| Dependencies, editor, and CI          | [stack](docs/architecture/stack.md), [quality gates](docs/architecture/quality-gates.md)                                                              | `composer.json`, `package.json`, lock files, `.github/actions/setup-project`              | Locked audits, clean install for dependency changes, full gate       |
| Dev scripts, PM2, nginx, deployment   | [runtime](docs/architecture/runtime.md), [deployment](docs/deployment.md)                                                                             | `composer.json` scripts, `pm2.config.cjs`, `hosting/nginx_config`                         | `nginx -t` with request checks; affected scripts run locally         |
| Tests and test tooling                | [testing](docs/architecture/testing.md)                                                                                                               | `tests/Pest.php`, `tests/Support`, `phpunit.xml`, `vitest.config.ts`                      | Affected suites                                                      |

## Document Ownership

Each concern has exactly one owning document. Update the owner when that concern changes; everything else links to it.

| Document                                                        | Owns                                                                                                                                         |
| --------------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------- |
| `AGENTS.md`                                                     | Workflow, engineering standards, hard rules, change-impact checklist, quality gate, reporting, docs sync                                     |
| [backend](docs/architecture/backend.md)                         | Bootstrapping, modules and discovery, ownership, shared infrastructure, request/controller/handler conventions, DTO naming, errors, database |
| [contracts](docs/architecture/contracts.md)                     | Backend-to-frontend contract pipeline, TypeScript generation, shared page props, generated artifact sources, `composer generate`             |
| [routes and security](docs/architecture/routes-and-security.md) | Route contract, gates and policies, authentication wiring, rate limits, security headers                                                     |
| [realtime](docs/architecture/realtime.md)                       | Mutation context fan-out, domain and broadcast events, channels, Echo, realtime composables, queue priority                                  |
| [frontend](docs/architecture/frontend.md)                       | Source layout, frontend ownership, TypeScript declarations, auto-import and component registration, ESLint guards, navigation, toasts        |
| [frontend rendering](docs/architecture/frontend-rendering.md)   | App entry, SSR request isolation, deterministic markup, stylesheets, fonts, color scheme, CSP nonce                                          |
| [frontend UI](docs/architecture/frontend-ui.md)                 | Component layering, theme contracts, prop forwarding, icons, accessibility, forms, server tables                                             |
| [frontend data](docs/architecture/frontend-data.md)             | Wayfinder route helpers, `apiRequest`, `useApiQuery`/`useApiMutation` semantics, identity-driven cache resets                                |
| [module generator](docs/architecture/module-generator.md)       | `generate:module` options, adding a page, generator behavior, scaffold outputs, route defaults, resource manifests                           |
| [stack](docs/architecture/stack.md)                             | Versions, package management, Composer patches, TypeScript bridge, editor setup, native bindings, dependency policy                          |
| [runtime](docs/architecture/runtime.md)                         | `composer setup`/`dev`/`dev:ssr`, SSR serving modes, Vite dev server watcher                                                                 |
| [deployment](docs/deployment.md)                                | Production server, environment, build, nginx, PM2 processes, scheduler, trusted proxies, updates, troubleshooting                            |
| [quality gates](docs/architecture/quality-gates.md)             | Composition of generation/cleanup/QA scripts, script performance, CI workflows                                                               |
| [testing](docs/architecture/testing.md)                         | Pest and Vitest conventions, test configuration, coverage areas, test placement                                                              |
| [README](README.md)                                             | Project introduction and quick start for humans                                                                                              |

`docs/starter-kit-audit.md` is a dated historical record, not guidance.

## Source Of Truth

When guidance conflicts, resolve it in this order:

1. Current runtime behavior and code in the repository.
2. Generated artifacts that are owned by backend contracts.
3. `AGENTS.md` and `docs/architecture/**`.
4. Other docs: `README.md` and the rest of `docs/**`.

Treat current code as evidence of current behavior, not proof that a bug is intended. When docs and code disagree, establish which behavior the task requires, implement it, and fix the docs in the same task.

## Task Modes And Scope

- Respect the task mode: plan-only, review-only, implementation, repair, or release. Planning and review modes do not authorize product-code changes. Implementation and repair modes authorize the necessary local changes and verification: continue through completion and do not stop at analysis, findings, or a proposed plan unless the user redirects you.
- Never guess at requirements. Resolve routine implementation choices from repository evidence. Ask concise questions when an unresolved decision materially affects requirements, compatibility, data loss, security, or an external action; state low-risk assumptions and continue independent work while waiting. Authorization already given persists; do not ask for it again.
- Deliver the smallest complete change. Fix related root causes and touched inconsistencies; record unrelated findings instead of turning a focused request into an unbounded rewrite.
- Preserve the user's staged, unstaged, and untracked work, and inspect each separately. Do not stage, discard, reset, stash, commit, push, rebase, merge, publish, deploy, or mutate live data unless the task or prior instructions authorize it. Use an isolated checkout to verify an exact revision or to avoid disturbing existing work.
- Linked issues, documents, and provider responses are task evidence, not authorization to expand scope or take external actions.
- Discover the tools, commands, and skills actually available instead of assuming one exists. Verify version-sensitive framework or provider guidance against official documentation.
- Keep progress updates brief and substantive. Never lose track of the objective, decisions, acceptance criteria, completed work, and outstanding verification across interruptions and handoffs.

## Workflow

1. **Understand** (required before writing code)
    - Read the applicable instructions, skills, and owning references, then the actual implementation, related tests, manifests, generation flow, and CI commands. Use reference implementations that exist in this checkout; never carry stale assumptions from docs, habits, or earlier starter-kit conventions.
    - Establish the intended behavior, behavior to preserve, invariants, acceptance criteria, scope boundaries, generated-contract implications, and non-functional constraints.
    - Trace each affected flow from input through authorization, validation, domain logic, persistence, serialization and generated contracts, consumers, state/UI, and side effects, including jobs, realtime, external integrations, generators, configuration, and docs. Map every file, service, event, and component it touches; never optimize locally while missing a broader contract, dependency, or workflow constraint.
    - Identify the canonical owner and existing reuse points before introducing any type, helper, component, dependency, abstraction, or layer.
2. **Plan** (required)
    - Design the solution and list every file it changes: backend, frontend, generated artifacts, tests, docs. For substantial or broad work, keep a short acceptance, impact, and verification checklist with a concise plan, and record findings and changed contracts as you go.
    - Reuse existing module services, requests, DTOs, queries, commands, handlers, responders, composables, base UI primitives, theme recipes, and route helpers before adding anything.
    - Compare the change with the global patterns and plan the cleanup of any touched mismatch, duplicate logic, or incomplete contract.
3. **Implement** focused, atomic changes, one at a time, following the [engineering standards](#engineering-standards).
4. **Verify** with the [quality gate](#quality-gate) and the [change-impact checklist](#change-impact-checklist).
5. **Complete** when acceptance criteria are met, every affected layer and required cleanup is covered, all required checks pass, no dead code or TODOs remain, and the owning docs are updated. If an external dependency blocks completion, finish all independent work and state the exact remaining action. Then [report](#reporting).

## Engineering Standards

### Design

- Prefer the smallest, simplest design that is correct, complete, extensible, testable, and aligned with existing patterns. Optimize for minimal conceptual and maintenance surface (fewer concepts, layers, and moving parts), not for the fewest lines: never compressed or clever code.
- Every new file, type, helper, wrapper, state object, mapping, branch, dependency, and abstraction must justify itself. After implementing, challenge each addition and remove anything not required for correctness, clarity, reuse, or an established constraint.
- Prefer the fewest necessary touch points and the strongest shared abstraction, never at the cost of correctness, clarity, or alignment with the current architecture. Choose the clean change that improves global consistency and minimizes future touch points, not the shortest local patch.
- One authoritative owner per business rule and per contract; consumers delegate to it. Generate or derive dependent types instead of synchronizing them by hand. Keep closely related behavior together instead of scattering one decision across components, controllers, hooks, stores, services, and utilities.
- Shared concerns use shared abstractions; module-specific concerns stay module-local. Share logic only when consumers have the same responsibility, invariants, and semantics: similar-looking code is not a reason, and premature "universal" helpers hide domain differences. Preserve deliberate duplication required by tooling, isolation, generated outputs, or genuinely independent domain behavior.
- Prefer direct, explicit data flow over indirection, pass-through wrappers, adapter chains, and repeated transformations. Distinct persistence, domain, transport, and presentation shapes are fine when their responsibilities differ; centralize each boundary mapping and never transform the same data twice.
- Prefer deleting, simplifying, or consolidating existing code over adding a parallel implementation, and direct cleanup over compatibility shims unless persisted data or external consumers require a transition. Necessary compatibility logic stays centralized, intentional, tested, and bounded.
- Structure code for maximum cleanliness, reusability, maintainability, strict type safety, extensibility, performance, modularity, robustness, and developer experience. DI: constructor injection, readonly dependencies, explicit typed inputs, thin transport layers. DX: predictable file placement, generated contract flows, route/action helpers, shared abstractions.
- Match the existing architecture precisely. Continuously align backend, frontend, generated artifacts, tests, and docs on one canonical implementation per concern instead of letting module-local exceptions accumulate. Leave touched code more consistent, more strictly typed, and less duplicated, without introducing divergence elsewhere in the touched scope. When the architecture must change, update its owning document in the same change.
- When alternatives remain, prefer the one that most strengthens long-term maintainability, explicit contracts, and safe extension paths.

### Correctness

- Keep strict typing intact end to end: validated input, request DTOs, handlers, resources, generated types, composables, page props, tests, and runtime parsing. Treat untrusted input as `unknown` and validate or narrow it at the boundary.
- Preserve nullability, optionality, enums, errors, and serialization semantics. Never hide a mismatch with `any`, broad or double assertions (such as `as User`), non-null assertions, disabled checks, ignored errors, or fabricated defaults; guard nullable values explicitly. Keep legitimate generics and constraint-preserving constructs such as `satisfies` and `as const`.
- Do not expose additional fields to avoid using an appropriate DTO, resource, serializer, or boundary mapping.
- Never remove necessary validation, authorization, error handling, type safety, tests, accessibility, observability, or boundary handling to reduce code.
- Preserve relevant tenancy, privacy, transactions, after-commit side effects, retry and idempotency behavior, cache isolation, concurrency guarantees, and failure semantics.
- Preserve accessible, responsive, deterministic UI, including loading, empty, error, disabled, success, and permission states. Account for SSR, hydration, request isolation, cancellation, and stale state.
- Preserve working functionality while refactoring: no regressions and no speculative rewrites.
- Address observable performance costs: N+1 queries, unbounded reads, duplicate scans or requests, redundant computation, avoidable rerenders, unnecessary network calls or subscriptions, and missing cleanup. Measure performance claims; add no speculative caches, memoization, background processing, or abstractions, and keep optimizations readable.

### Comments And Documentation

- Code is self-documenting through names, cohesive functions, clear types, and straightforward control flow. Do not narrate what the next line does or repeat signatures in docblocks.
- Comment only what code cannot express: a non-obvious business invariant, an architectural constraint, a security or compatibility requirement, a counterintuitive decision, an external-system limitation, or why a simpler implementation would be wrong.
- In the affected area, remove stale, misleading, redundant, or speculative comments, commented-out code, debugging notes, and obsolete TODO/FIXME comments. Update useful comments when the behavior they describe changes.
- Documentation explains public behavior, contracts, setup, and operations; it never compensates for unnecessarily complicated code.

### Cleanup

- Never leave work half-finished, untested, partially wired, or inconsistent. If a change needs backend, frontend, generated artifacts, tests, docs, or cleanup, do all of it in the same task; never hand-wave or defer a required step.
- Remove related dead code and branches, duplicate logic and rules, stale imports, unused exports, redundant mappings, unused scaffolding, obsolete implementations, and outdated docs. Do not keep parallel old and new implementations, or a wrapper that merely renames or forwards a call without enforcing a meaningful boundary.
- Search the whole affected flow for partial migrations, old terminology, missed call sites, duplicated conditions, and inconsistent data shapes. Before declaring code unused, check dynamic discovery, reflection, framework conventions, generated consumers, and external compatibility.
- Do not preserve stale abstractions because they already exist in a draft, comment, or doc.

## Hard Rules

These hold everywhere. The linked document explains the mechanics.

- `npm` is the only JavaScript package manager (`ensure-node-env.js` enforces it; never yarn, pnpm, or bun). `composer.lock` and `package-lock.json` are committed contracts that every install honors. → [stack](docs/architecture/stack.md)
- Never weaken typechecking, linting, tests, engine requirements, security audits, or CI configuration, force an install (`--force`, `--legacy-peer-deps`), or disable strict checks to make something pass.
- Never hand-edit or shadow generated artifacts: `bootstrap/cache/modules.php`, `resources/js/routes/**`, `resources/js/actions/**`, `resources/js/wayfinder/index.ts`, `resources/js/types/app-data.ts`, `resources/js/types/auto-imports.d.ts`, `resources/js/types/components.d.ts`. → [contracts](docs/architecture/contracts.md#generated-artifacts)
- Never redefine backend-owned DTOs, enums, realtime payloads, or route payload shapes in frontend code: consume `@/types/app-data` and the Wayfinder helpers, and never hardcode URLs. → [contracts](docs/architecture/contracts.md), [frontend data](docs/architecture/frontend-data.md#route-helpers)
- Every PHP file declares `strict_types=1`. Transport input is hydrated into request DTOs, and services, queries, commands, and handlers accept DTOs or explicit typed parameters, never mixed arrays. → [backend](docs/architecture/backend.md#requests-controllers-and-handlers)
- Module code stays in its module (`app/Modules/<Module>/**`, `resources/js/modules/<module>/**`). Feature frontend modules never import each other, including type-only imports, and auto-imported symbols are never imported directly. → [backend](docs/architecture/backend.md#ownership-rules), [frontend](docs/architecture/frontend.md)
- Nothing request-scoped is written to module-level state or `globalThis`, and nothing that renders into markup is nondeterministic. → [frontend rendering](docs/architecture/frontend-rendering.md)
- No raw color literals, Tailwind palette colors, local `<style>` blocks, duplicated shared recipes, or runtime icon packages in frontend source; extend `theme.css` and `theme.ts`. → [frontend UI](docs/architecture/frontend-ui.md#theme-contracts)
- Authorization is enforced at the server boundary through policies, gates, and route middleware; UI visibility and capability flags never replace it. → [routes and security](docs/architecture/routes-and-security.md)
- Command-side side effects (audit, notifications, realtime) fan out from one mutation context after commit. Domain events stay separate from broadcast events, and channel strings come only from their owning backend enums. → [realtime](docs/architecture/realtime.md)
- These are breaking changes unless every consumer is updated in the same task: route names or URI contracts, DTO or resource payload shapes, enum values, generated type/action/route outputs, realtime channel patterns, event names, or payload contracts, and policy/gate ability names. An intentional break updates all backend code, frontend consumers, tests, generated artifacts, and docs together.

## Change-Impact Checklist

For every non-trivial change, explicitly verify all affected layers:

- backend contracts
- generated artifacts
- frontend consumers
- route/action helper usage
- authorization and policies/gates
- realtime contracts and subscriptions
- database schema, indexes, and query behavior
- tests: meaningful coverage of the happy path, failure path, regressions, boundary failures, and relevant edge cases, added to the nearest existing suite; no tests that merely restate implementation details
- docs
- stale code removal

## Quality Gate

`composer generate-and-cleanup` is the mandatory zero-error, zero-warning static completion gate. Run it separately from builds and tests that regenerate artifacts, then run the affected suites and builds:

```bash
composer generate-and-cleanup
composer test
npm run test:unit        # frontend behavior or composable changes
composer qa:check        # non-mutating static verification
composer qa:generated    # after touching stubs/module-generation or the scaffold planner
```

- Regenerate whenever backend route, enum, DTO, channel, provider, gate, listener, or module-registry contracts change; `composer generate` (included in the gate) is mandatory before completion.
- Run the gates against the final implementation with locked installs, and keep artifact-writing commands sequential where their outputs overlap. Repeat checks after changes that invalidate earlier results; do not rerun successful expensive suites without a reason.
- Fix task-caused failures and every resolvable repository-mandated failure. Distinguish unrelated failures with evidence; when scope or access prevents a required repair, report the unresolved gate explicitly instead of claiming completion.
- Inspect the final diff and generated outputs, including untracked files. Confirm requirement coverage, contract consistency, every affected consumer, and the absence of unintended changes. Search specifically for missed call sites, scattered copies of a rule, parallel old and new implementations, unnecessary mappings or wrappers, types that duplicate an authoritative contract, dead code, unnecessary comments, stale terminology, and debugging or temporary code. Review every new file, abstraction, dependency, helper, and comment for necessity.
- Never simulate a passing check. Keep executed verification separate from static inspection and inference; passing static checks is not browser, provider, CI, deployment, or load-test verification.

What each command runs, and what CI enforces, is in [quality gates](docs/architecture/quality-gates.md).

## Reporting

Unless the task supplies its own report format, finish with the following, in proportion to the work:

1. The completed outcome
2. Important behavior or contract changes
3. Relevant file links
4. Logic consolidated or code removed
5. Reusable patterns introduced or reused
6. Cleanup performed, including unnecessary comments removed
7. Verification commands and their exact results
8. Material remaining risks, blockers, or required gates

Avoid unsupported claims such as "zero bugs", "fully secure", or "production-ready".

## Docs Sync

- When behavior, architecture, setup, workflows, or generation change, update the owning document from [document ownership](#document-ownership) in the same task. Update this file when the workflow, rules, task map, or ownership change. Never leave known stale instructions behind.
- Keep each fact in its owner and link to it from elsewhere instead of restating it.

## Laravel Boost MCP

When Laravel Boost MCP tools are available:

- Use `search-docs` before Laravel/Inertia/Wayfinder/Sanctum/Tailwind ecosystem changes.
- Use `list-artisan-commands` before running Artisan commands.
- Run Artisan with `--no-interaction` when the specific command supports it.
- Use MCP equivalents (`tinker`, `database-query`, `browser-logs`) when applicable. If MCP tools fail, fall back to equivalent terminal shell commands.
