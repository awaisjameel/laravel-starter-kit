# Backend-Owned Contracts And Generated Artifacts

Read before adding or changing a DTO, enum, shared page prop, route, channel, provider, gate, or listener, or anything that feeds a generated artifact.

## Contract Pipeline

1. Define or update the backend enum / DTO.
2. Add `#[TypeScript]` if the frontend consumes it.
3. Use it in requests, queries, commands, handlers, controllers, resources, and realtime payloads.
4. Run generation commands.
5. Consume the generated contract from `@/types/app-data`.
6. Update tests.

## TypeScript Generation

- Any backend DTO or enum consumed by the frontend must be exported through the TypeScript transformer.
- Annotate frontend-facing DTOs/enums with `#[TypeScript]`. Enums under `app/**` are collected regardless of the attribute.
- TypeScript generation is configured in `app/Providers/TypeScriptTransformerServiceProvider.php`. Transformer v3 has no config file; do not reintroduce `config/typescript-transformer.php`.
- That provider is the single place that owns the generated contract surface. Current settings:
    - `DataClassTransformer()` so nullable DTO properties remain `foo: T | null`, matching serialized responses; optional fields must use explicit backend `Optional`/`Lazy` semantics
    - `EnumTransformer(useUnionEnums: false)` so PHP enums become native TypeScript enums
    - `FlatModuleWriter('app-data.ts')` into `resources/js/types`, keeping one flat ES module
    - `withoutManifest()` so no transformer manifest file lands in `resources/js/types`
- Route and controller type generation from the transformer stays off. Wayfinder owns that surface; see [route helpers](frontend-data.md#route-helpers).

## Shared Page Props

- Current shared auth user contract is `App\Modules\Shared\Data\UserViewData|null`; do not serialize the raw user model into Inertia props.
- `SharedPageData` and its nested `SharedAuthData`, `SharedFlashData`, and `SharedQuoteData` own application-wide Inertia props. `AppPageProps` composes the generated type with page-specific props. Flash fields are always present as `string|null`; do not redeclare these shapes in TypeScript.
- The frontend binding of these props is described in [frontend](frontend.md#typescript-declarations).

## Generated Artifacts

The generated paths are listed in the [hard rules](../../AGENTS.md#hard-rules). They come from two sources:

- `composer generate` writes the module manifest, `app-data.ts`, and the Wayfinder routes, actions, and runtime.
- Vite writes `auto-imports.d.ts` and `components.d.ts` from `frontend-auto-import.config.mjs` and the component directories.

All of them are committed. After changing backend contracts, auto-import configuration, or component files, sync them with:

```bash
composer generate
npm run build:ssr
```

CI fails on any drift; see [CI workflows](quality-gates.md#ci-workflows). Wayfinder output depends on two local [Composer patches](stack.md#composer-patches).

## Generation Command

`composer generate` currently runs:

- `php artisan modules:cache --no-interaction`
- `php artisan typescript:transform`
- `php artisan wayfinder:generate`
