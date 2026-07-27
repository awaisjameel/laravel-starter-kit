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

import {
    autoImportDirs,
    autoImportImports,
    componentAutoImportOptions,
    iconComponentPrefix,
    inertiaComponentResolver
} from './frontend-auto-import.config.mjs'

const ssrEntry = 'resources/js/ssr.ts'
// The stylesheet is its own entry rather than an import inside `app.ts`. SSR ships
// a fully rendered document, so the browser paints as soon as the HTML lands: if the
// CSS only arrived through the JS module graph the page would render unstyled first
// and reflow once the bundle evaluated. As a separate entry, `@vite` emits a
// render-blocking `<link rel="stylesheet">` in both dev and production instead.
const cssEntry = 'resources/css/app.css'
const jsDirectory = fileURLToPath(new URL('./resources/js', import.meta.url))

export default defineConfig({
    plugins: [
        wayfinder({
            command: 'php artisan wayfinder:generate --no-interaction'
        }),
        laravel({
            input: [cssEntry, 'resources/js/app.ts'],
            ssr: ssrEntry,
            refresh: true
        }),
        // Serves SSR from the Vite dev server (no separate node process in dev),
        // warms up page modules, and wraps `resources/js/ssr.ts` for production.
        inertia({
            ssr: {
                entry: ssrEntry,
                cluster: true
            }
        }),
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
            vueTemplate: true,
            viteOptimizeDeps: true,
            dts: 'resources/js/types/auto-imports.d.ts',
            dtsMode: 'overwrite',
            imports: autoImportImports,
            dirs: autoImportDirs
        }),
        Icons({
            compiler: 'vue3',
            autoInstall: true
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
            '@': jsDirectory,
            '/resources/js': jsDirectory
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
                // about the entry files. Only those entries are affected and the
                // plugin owns the transform, so there is nothing to fix here.
                if (warning.code === 'SOURCEMAP_BROKEN' && warning.plugin === '@inertiajs/vite') {
                    return
                }

                defaultHandler(warning)
            }
        }
    }
})
