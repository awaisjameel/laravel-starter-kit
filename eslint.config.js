import prettier from 'eslint-config-prettier'
import vue from 'eslint-plugin-vue'

import { defineConfigWithVueTs, vueTsConfigs } from '@vue/eslint-config-typescript'

const autoImportMessage = 'Manual import is not needed here. This project auto-imports this symbol; remove the import and rely on auto-import.'

export default defineConfigWithVueTs(
    vue.configs['flat/essential'],
    vueTsConfigs.recommended,
    {
        ignores: [
            'vendor/**',
            'node_modules/**',
            'public/**',
            'bootstrap/ssr/**',
            'tailwind.config.js',
            'resources/js/components/ui/**',
            'resources/js/actions/**',
            'resources/js/routes/**',
            'resources/js/wayfinder/**',
            'resources/js/types/app-data.ts',
            'resources/js/types/auto-imports.d.ts',
            'resources/js/types/components.d.ts'
        ]
    },
    {
        rules: {
            'vue/multi-word-component-names': 'off',
            '@typescript-eslint/no-explicit-any': 'off',
            'no-restricted-imports': [
                'warn',
                {
                    paths: [
                        {
                            name: '@inertiajs/vue3',
                            importNames: ['usePage', 'useForm', 'useRemember', 'usePoll', 'router', 'Deferred', 'Head', 'Link'],
                            message: autoImportMessage
                        },
                        {
                            name: 'ziggy-js',
                            importNames: ['Ziggy'],
                            message: autoImportMessage
                        }
                    ],
                    patterns: [
                        {
                            group: ['@/composables/*', '@/composables/**'],
                            message: autoImportMessage
                        },
                        {
                            group: ['@/stores/*', '@/stores/**'],
                            message: autoImportMessage
                        },
                        {
                            group: ['@/components/**/*.vue', '@/layouts/**/*.vue'],
                            message: autoImportMessage
                        }
                    ]
                }
            ]
        }
    },
    prettier
)
