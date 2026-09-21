import inertia from '@inertiajs/vite'
import { wayfinder } from '@laravel/vite-plugin-wayfinder'
import tailwindcss from '@tailwindcss/vite'
import vue from '@vitejs/plugin-vue'
import laravel from 'laravel-vite-plugin'
import { fileURLToPath, URL } from 'node:url'
import AutoImport from 'unplugin-auto-import/vite'
import IconsResolver from 'unplugin-icons/resolver'
import Icons from 'unplugin-icons/vite'
import Components from 'unplugin-vue-components/vite'
import { defineConfig } from 'vite'

import { autoImportOptions, componentAutoImportOptions, iconComponentPrefix, inertiaComponentResolver } from './frontend-auto-import.config.mjs'

const appEntry = 'resources/js/app.ts'

export default defineConfig({
    plugins: [
        wayfinder({
            command: 'php artisan wayfinder:generate --no-interaction'
        }),
        laravel({
            // The stylesheet is its own entry so `@vite` emits a render-blocking
            // `<link>`: server-rendered markup must never paint before its CSS.
            input: ['resources/css/app.css', appEntry],
            ssr: appEntry,
            refresh: true
        }),
        // Serves SSR from the Vite dev server and wraps `app.ts` for the SSR build.
        inertia(),
        tailwindcss(),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false
                }
            }
        }),
        AutoImport({
            ...autoImportOptions,
            viteOptimizeDeps: true,
            dts: 'resources/js/types/auto-imports.d.ts',
            dtsMode: 'overwrite'
        }),
        Icons({
            compiler: 'vue3'
        }),
        Components({
            ...componentAutoImportOptions,
            dts: 'resources/js/types/components.d.ts',
            resolvers: [
                inertiaComponentResolver,
                IconsResolver({
                    prefix: iconComponentPrefix
                })
            ]
        })
    ],
    resolve: {
        alias: {
            '@': fileURLToPath(new URL('./resources/js', import.meta.url))
        }
    },
    server: {
        watch: {
            // Watching PHP dependencies and runtime storage creates tens of thousands
            // of file watchers, which blocks the dev server for long enough that the
            // SSR warm-up and the first requests from Laravel time out.
            ignored: ['**/storage/**', '**/vendor/**', '**/bootstrap/ssr/**', '**/public/build/**']
        }
    },
    build: {
        rolldownOptions: {
            checks: {
                // These transforms intentionally own most of this small app's build
                // work. Rolldown's percentage-based advisory is therefore noisy even
                // when the complete production build finishes in a few seconds.
                pluginTimings: false
            },
            onwarn: (warning, defaultHandler) => {
                // `@inertiajs/vite` enables sourcemaps for the SSR build but rewrites
                // the `pages` shorthand without emitting one, so every build warns
                // about the entry file. Only that entry is affected and the plugin
                // owns the transform, so there is nothing to fix here.
                if (warning.code === 'SOURCEMAP_BROKEN' && warning.plugin === '@inertiajs/vite') {
                    return
                }

                defaultHandler(warning)
            }
        }
    }
})
