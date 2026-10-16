# Mutations, Events, And Realtime

Read before changing command-side side effects (audit logging, notifications, broadcasts), domain or broadcast events, listeners, channels, Echo configuration, or realtime composables.

Channel pattern enums, event-name enums, presence payloads, and broadcast payload DTOs are generated into `resources/js/types/app-data.ts` with the rest of the [backend-owned contracts](contracts.md).

## Mutation Fan-Out

- Reference flow for one mutation context consumed by listeners:
    - `UserActionContext::fromRequest(...)`
    - `MutationMetadata::fromRequest(...)`
    - `MutationContext`
    - `UserManagementEvent`
    - listeners under `app/Modules/Users/Listeners/**`
- `UserManagementEvent` implements `ShouldDispatchAfterCommit`. Dispatch it after persistence succeeds; rolled-back transactions must not emit audit, notification, or realtime side effects. `UserActionContext` snapshots actor and target models so later changes within the transaction cannot rewrite an earlier event. Deleted-user broadcasts carry `user: null`.
- Listeners translate domain events into realtime broadcasts and notifications.
- Realtime events should extend `app/Modules/Shared/Realtime/Events/RealtimeEvent`.
- `RealtimeEvent` currently broadcasts:
    - on queue `realtime`
    - with `afterCommit = true`
- Realtime dispatch should go through `App\Modules\Shared\Realtime\Contracts\RealtimeDispatcher`.

## Backend

- Reverb is the current default broadcaster in `.env.example`.
- Shared notification channel authorization is defined in `app/Modules/Shared/Routes/channels.php`.
- Users realtime channel authorization is defined in `app/Modules/Users/Routes/channels.php`.
- Root channel aggregation lives in `routes/channels.php`.
- Current backend-owned realtime enums:
    - `App\Modules\Shared\Enums\SharedRealtimeChannel`
    - `App\Modules\Users\Enums\UsersRealtimeChannel`
    - `App\Modules\Users\Enums\UsersRealtimeEvent`
    - `App\Modules\Users\Enums\UsersRealtimeAction`
- Presence member payload contract:
    - `App\Modules\Shared\Realtime\Data\PresenceMemberData`
- Broadcast notification payload contract:
    - `App\Modules\Users\Data\UserManagementNotificationData`
- Channel registration, broadcast destinations, notification routing, and frontend subscriptions all consume the canonical channel enums. Resolve parameterized destinations through `ChannelPatternResolver`.

## Frontend

- Initialize Echo only through `configureRealtime()` in `resources/js/lib/realtime/config.ts`, which `resources/js/app.ts` calls once for both the client and SSR.
- `configureRealtime()` is SSR-aware: under `import.meta.env.SSR` it configures the `null` broadcaster, so realtime composables resolve to inert channels instead of throwing or opening a WebSocket while the server renders. Never make a realtime composable depend on a live connection at `setup()` time.
- Shared realtime frontend helpers live in:
    - `resources/js/lib/realtime/config.ts`
    - `resources/js/lib/realtime/channels.ts`
    - `resources/js/composables/useRealtime.ts`
    - `resources/js/composables/useRealtimeConnection.ts`
- Current shared realtime composables:
    - `useRealtimeEvent`
    - `useRealtimeModel`
    - `useRealtimeNotification`
    - `useRealtimePresence`
    - `useRealtimeConnection`
- Derive channel strings from backend-owned patterns through `resolveRealtimeChannel(...)`.
- Feature-level usage helpers should live in module-local `contracts/realtime.ts`.
- The current reference implementation is `resources/js/modules/users/contracts/realtime.ts`.
- Socket ID propagation for `toOthers()` belongs to [`apiRequest`](frontend-data.md#api-requests).

## Runtime

- Queue workers must prioritize `realtime,high,default`.
- Reverb runs alongside the queue workers in local development ([runtime](runtime.md)) and production ([deployment](../deployment.md)).
- Frontend realtime config currently supports:
    - session auth via `/broadcasting/auth` and `/broadcasting/user-auth`
    - bearer auth via `/api/broadcasting/auth` and `/api/broadcasting/user-auth`
- Do not hand-attach broadcast auth endpoints in feature code.
