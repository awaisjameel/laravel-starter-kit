import vue from '@vitejs/plugin-vue'
import { fileURLToPath, URL } from 'node:url'
import AutoImport from 'unplugin-auto-import/vite'
import { defineConfig } from 'vitest/config'
import { autoImportDirs, autoImportImports } from './frontend-auto-import.config.mjs'

export default defineConfig({
    plugins: [
        vue(),
        AutoImport({
            dts: false,
            imports: autoImportImports,
            dirs: autoImportDirs
        })
    ],
    resolve: {
        alias: {
            '@': fileURLToPath(new URL('./resources/js', import.meta.url)),
            '/resources/js': fileURLToPath(new URL('./resources/js', import.meta.url))
        }
    },
    test: {
        environment: 'jsdom',
        include: ['resources/js/**/*.test.ts']
    }
})
