# Frontend Data, Requests, And Caching

Read before changing route helper usage, `apiRequest`, `useApiQuery`, `useApiMutation`, query-cache behavior, or how client state reacts to authentication changes.

## Route Helpers

Wayfinder's generated route/action helpers are the only route surface. Ziggy is deliberately not a dependency: its `route()` is string-keyed rather than type-checked, and shipping its route table in every Inertia response duplicates what Wayfinder already generates at build time.

## API Requests

- Use `useApiQuery`, `useApiMutation`, and `apiRequest` for API-driven state. Update existing state deterministically instead of reloading, refetching, or adding watchers, and reuse the shared caches, query utilities, and realtime invalidation hooks instead of duplicating fetch logic.
- `apiRequest` callers must validate payloads with `parseResponse` when a typed runtime contract matters.
- `apiRequest` is the canonical place for `X-Socket-ID` propagation, so broadcast listeners can call `toOthers()` safely.
- Same-origin mutations read the current `XSRF-TOKEN` cookie into `X-XSRF-TOKEN`; no static CSRF meta tag is needed. Automatic CSRF and socket headers must not be sent to another origin. Existing URL queries and fragments must survive added query options.
- `useApiQuery` is client-only by design: it never reads, writes, or fetches into its module-level cache or in-flight request map during SSR. Server-rendered page data must arrive through Inertia props.

## `useApiQuery` Semantics

- Concurrent consumers of the same cache key share one request, including a forced `refresh()`. Only the raw fetch is shared; `select` and `mapError` stay per caller.
- `queryCache` stores the raw `queryFn` result, never a `select` projection, so one entry can serve consumers that project the same key differently. Keep each key tied to one raw response shape. `getApiQueryCacheData`/`setApiQueryCacheData` operate on that raw shape.
- Invalidations and explicit cache writes version the key and drop its in-flight entry, so a request that started earlier cannot be joined or overwrite newer/optimistic data when it settles.
- A query key change drops the previous key's data while the new result loads. A request for an earlier reactive key may populate that key's cache, but it cannot overwrite the composable state for the current key.
- String and array keys have distinct serialized identities, even when a string looks like serialized JSON. Delayed retries stop after a key change, invalidation, or identity-driven cache clear so they cannot read new inputs under an old key.
- A projected result type requires an explicit `select`; identity queries preserve `TData` and cannot assert an unrelated result type.
- A disabled query is never `isLoading`; disabling it supersedes observer updates from in-flight work, and `isSuccess` additionally requires resolved data.

## Mutations

- Query/mutation error types incompatible with `ApiError` require `mapError`.
- Mutation pending state covers all concurrent requests and settles when the outstanding operations finish; the latest invocation owns displayed data/errors. Reset clears displayed state without pretending outstanding work has stopped.
- Callbacks run once per invocation. A successful write invalidates its cache keys before success callbacks run. Callback failures propagate to the caller without reclassifying the successful write as failed or invoking rollback, and settlement still runs once when a success or error callback throws.

## Identity Changes

- `resources/js/components/AppRoot.vue` clears the query cache synchronously when authenticated identity changes; keep this lifecycle there. Account data must not survive logout or account switching.
- A full clear synchronously resets mounted query and mutation data and errors; pending query completions cannot restore them. Enabled queries fetch again on an explicit refresh or a key/enabled change.
- Each mutation captures its auth context and checks it before sending a write (including after asynchronous preparation) and between asynchronous callbacks. Stale contexts reject with `stale_auth_context` (or the custom mapped error) and skip subsequent callbacks and invalidations belonging to the old account.
- A reset cannot cancel a callback that is already running, and a write already sent may have persisted; the rejection does not imply server rollback.
