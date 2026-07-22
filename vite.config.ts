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

import { autoImportDirs, autoImportImports } from './frontend-auto-import.config.mjs'

const ssrEntry = 'resources/js/ssr.ts'
const jsDirectory = fileURLToPath(new URL('./resources/js', import.meta.url))

export default defineConfig({
    plugins: [
        wayfinder({
            command: 'php artisan wayfinder:generate --no-interaction'
        }),
        laravel({
            input: ['resources/js/app.ts'],
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
            deep: true,
            extensions: ['vue'],
            collapseSamePrefixes: true,
            directoryAsNamespace: true,
            globalNamespaces: ['components'],
            dts: 'resources/js/types/components.d.ts',
            dirs: ['resources/js/components', 'resources/js/layouts', 'resources/js/modules'],
            resolvers: [
                (componentName) => {
                    if (['Link', 'Head'].includes(componentName)) {
                        return { name: componentName, from: '@inertiajs/vue3' }
                    }
                },
                IconsResolver({
                    prefix: 'Icon'
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
        rollupOptions: {
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
