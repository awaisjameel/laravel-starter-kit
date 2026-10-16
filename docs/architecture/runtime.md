# Local Runtime

Read before changing Composer dev scripts, SSR serving, or the Vite dev server configuration. Production processes, PM2, the scheduler, nginx, and trusted proxies are owned by [deployment](../deployment.md).

## Setup And Development

- `composer setup` is Laravel's first-run script: install both locked dependency graphs, create `.env`, generate the key, migrate, and build assets.
- `.env.example` keeps Laravel's `APP_URL=http://localhost`, and `php artisan serve` keeps its default `127.0.0.1:8000`, which Sanctum's default stateful domains already cover. When serving on another host or port, set `APP_URL` to that origin so first-party API requests stay stateful.
- `composer dev` starts:
    - `php artisan serve`
    - `php artisan queue:listen --queue=realtime,high,default --tries=1 --timeout=0`
    - `php artisan pail --timeout=0`
    - `npm run dev`
    - `php artisan reverb:start --host=0.0.0.0 --port=8080 --hostname=127.0.0.1 --no-interaction`
- `composer dev:ssr` builds SSR assets, then starts:
    - `php artisan serve`
    - `php artisan queue:listen --queue=realtime,high,default --tries=1 --timeout=0`
    - `php artisan pail --timeout=0`
    - `php artisan inertia:start-ssr`
    - `php artisan reverb:start --host=0.0.0.0 --port=8080 --hostname=127.0.0.1 --no-interaction`
- `vite.config.ts` excludes `storage`, `vendor`, and the build output directories from the dev server's file watcher. Watching them creates tens of thousands of watchers; on Windows that blocked the dev server for 20 seconds to several minutes on start, long enough for the SSR warm-up to time out and for `php artisan serve` to hit its 30-second limit. Keep new non-frontend directories with many files out of the watcher the same way. Vite's dependency optimizer discovers every bare import in its first pass, so no `optimizeDeps.include` list is maintained.

## Server-Side Rendering

SSR is served two different ways and both are wired up:

- Development: `@inertiajs/vite` exposes `/__inertia_ssr` on the Vite dev server and `inertia-laravel` routes to it automatically while Vite is hot. `composer dev` therefore renders pages server-side with HMR and no extra process.
- Production / `composer dev:ssr`: `npm run build:ssr` emits `bootstrap/ssr/app.js`, which Inertia's bundle detector finds without configuration, and `php artisan inertia:start-ssr` serves it as a single Node process.

`INERTIA_SSR_ENABLED` in `.env.example` toggles both paths; set it to `false` to turn SSR off. The rules code must follow to render on the server are in [frontend rendering](frontend-rendering.md).
