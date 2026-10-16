# Routes, Authorization, And Security

Read before adding or changing a route, middleware, gate, policy, rate limiter, authentication/session behavior, or security header.

Route names and URIs are domain-prefixed (`marketing.*`, `auth.*`, `app.*`, `api.v1.*`) and intentionally break from legacy starter-kit conventions. Content Security Policy details that exist for SSR and assets are owned by [frontend rendering](frontend-rendering.md#content-security-policy).

## Web Routes

- Marketing:
    - `GET /` -> `marketing.home`
- Auth guest routes under `/auth`:
    - `GET /auth/register` -> `auth.register.create`
    - `POST /auth/register` -> `auth.register.store`
        - middleware: `guest`, `throttle:auth-sensitive`
    - `GET /auth/login` -> `auth.login.create`
    - `POST /auth/login` -> `auth.login.store`
    - `GET /auth/forgot-password` -> `auth.password.request`
    - `POST /auth/forgot-password` -> `auth.password.email`
        - middleware: `guest`, `throttle:auth-sensitive`
    - `GET /auth/reset-password/{token}` -> `auth.password.reset`
    - `POST /auth/reset-password` -> `auth.password.store`
        - middleware: `guest`, `throttle:auth-sensitive`
- Authenticated auth routes under `/auth`:
    - `GET /auth/verify-email` -> `auth.verification.notice`
    - `GET /auth/verify-email/{id}/{hash}` -> `auth.verification.verify`
        - middleware: `auth`, `signed`, `throttle:6,1`
    - `POST /auth/email/verification-notification` -> `auth.verification.send`
        - middleware: `auth`, `throttle:auth-sensitive`
    - `GET /auth/confirm-password` -> `auth.password.confirm`
    - `POST /auth/confirm-password` -> `auth.password.confirm.store`
    - `POST /auth/logout` -> `auth.logout`
- App shell:
    - `GET /app/dashboard` -> `app.dashboard`
        - middleware: `auth`, `verified`
- Settings routes under `/app/settings`:
    - `GET /app/settings/profile` -> `app.settings.profile.edit`
    - `PATCH /app/settings/profile` -> `app.settings.profile.update`
    - `DELETE /app/settings/profile` -> `app.settings.profile.destroy`
    - `GET /app/settings/password` -> `app.settings.password.edit`
    - `PUT /app/settings/password` -> `app.settings.password.update`
    - `GET /app/settings/appearance` -> `app.settings.appearance`
    - `GET /app/settings` redirects to `/app/settings/profile`
    - middleware: `auth`
- Admin users routes under `/app/admin/users`:
    - `GET /app/admin/users` -> `app.admin.users.index`
    - `POST /app/admin/users` -> `app.admin.users.store`
    - `PUT /app/admin/users/{user}` -> `app.admin.users.update`
    - `PATCH /app/admin/users/{user}` -> `app.admin.users.update`
    - `DELETE /app/admin/users/{user}` -> `app.admin.users.destroy`
    - middleware: `auth`, `verified`, `can:manage-users`

## API Routes

- API broadcast auth endpoints defined in `routes/api.php`:
    - `GET|POST /api/broadcasting/auth`
    - `GET|POST /api/broadcasting/user-auth`
    - middleware: `auth:sanctum`
    - CSRF verification removed for these endpoints
- Versioned API under `/api/v1`
- Current user endpoint:
    - `GET /api/v1/me` -> `api.v1.me.show`
    - middleware: `auth:sanctum`
- Admin users API under `/api/v1/admin/users`:
    - `GET /api/v1/admin/users` -> `api.v1.admin.users.index`
    - `POST /api/v1/admin/users` -> `api.v1.admin.users.store`
    - `PUT /api/v1/admin/users/{user}` -> `api.v1.admin.users.update`
    - `DELETE /api/v1/admin/users/{user}` -> `api.v1.admin.users.destroy`
    - middleware: `auth:sanctum`, `can:manage-users`

## Gates And Policies

- `AuthServiceProvider` registers policies from `ModuleRegistry::policyMap(...)`.
- `AuthServiceProvider` requires all discovered module gate files.
- Current module gate:
    - `manage-users` in `app/Modules/Users/Routes/gates.php`
- Current module policy:
    - `app/Modules/Users/Policies/UserPolicy.php`

## Authentication Wiring

- Guest redirects are configured centrally in `bootstrap/app.php` to `route('auth.login.create')`, and authenticated users visiting guest-only routes are redirected to `route('app.dashboard')`. Laravel's own fallback looks for a route named `dashboard` or `home`, which the namespaced route names do not provide.
- The `verified` middleware alias is configured there with Laravel's `EnsureEmailIsVerified::redirectTo('auth.verification.notice')`. Keep this central default so existing and generated protected routes share the namespaced verification prompt; JSON requests retain Laravel's 403 response.
- Inertia history encryption defaults to enabled (`INERTIA_ENCRYPT_HISTORY=true`). Login, registration, logout, and account deletion call `Inertia::clearHistory()` after changing the session so the next page rotates the browser history key; Back navigation then requests protected data from the server instead of restoring the previous account's encrypted snapshot. Query-cache clearing does not replace this protection. Encryption needs a secure context (HTTPS or localhost; see [deployment](../deployment.md#choose-a-topology)), and an explicit `INERTIA_ENCRYPT_HISTORY=false` override disables it. See [Inertia history encryption](https://inertiajs.com/docs/v3/security/history-encryption).
- `statefulApi()` enables Sanctum session authentication and CSRF protection for API requests from configured first-party origins. Keep `SANCTUM_STATEFUL_DOMAINS` aligned with deployed frontend hosts, including ports. External clients use bearer tokens. Authentication regressions must exercise persisted cookies or real tokens; `actingAs` alone cannot verify middleware wiring.

## Security Rules

- Sensitive auth endpoints must use the `auth-sensitive` rate limiter.
- Password confirmation uses this limiter in addition to registration, reset, and verification notification endpoints.
- `AppServiceProvider` currently defines `auth-sensitive` as `5` requests per minute per IP.
- The `User` model invalidates email verification whenever its email changes through a model update, covering profile and admin transports. Bulk SQL updates bypass model events and must preserve this invariant explicitly.
- Security headers must continue to be set by `App\Http\Middleware\SecurityHeaders`.
- Current headers include:
    - CSP
    - `Referrer-Policy`
    - `X-Content-Type-Options`
    - `X-Frame-Options`
    - `Permissions-Policy`
- Non-production CSP is intentionally looser for local development.
- Audit and mutation metadata must preserve useful context while avoiding sensitive-field leakage.
