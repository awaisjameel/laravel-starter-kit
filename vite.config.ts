import tailwindcss from '@tailwindcss/vite'
import vue from '@vitejs/plugin-vue'
import laravel from 'laravel-vite-plugin'
import path from 'path'
import { defineConfig } from 'vite'

import { wayfinder } from '@laravel/vite-plugin-wayfinder'
import { autoImportDirs, autoImportImports } from './frontend-auto-import.config.mjs'
import AutoImport from 'unplugin-auto-import/vite'
import IconsResolver from 'unplugin-icons/resolver'
import Icons from 'unplugin-icons/vite'
import Components from 'unplugin-vue-components/vite'

export default defineConfig({
    plugins: [
        wayfinder({
            command: 'php artisan wayfinder:generate && npx prettier -w ./resources/js/**/*.{ts,js}'
        }),
        laravel({
            input: ['resources/js/app.ts'],
            ssr: 'resources/js/ssr.ts',
            refresh: true
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
            dts: 'resources/js/types/components.d.ts',
            dirs: ['resources/js/components', 'resources/js/layouts'],
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
            '@': path.resolve(__dirname, './resources/js')
        }
    }
})
