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
            '@typescript-eslint/no-explicit-any': 'error',
            'no-restricted-syntax': [
                'error',
                {
                    selector:
                        "TSAsExpression[typeAnnotation.type='TSTypeReference'][typeAnnotation.typeName.name='Record'][expression.type='TSAsExpression'][expression.typeAnnotation.type='TSUnknownKeyword']",
                    message: 'Avoid `as unknown as Record<string, unknown>`. Refactor the receiving type to accept the source type directly.'
                },
                {
                    selector: "VariableDeclarator[id.name='fields'][init.type='ArrayExpression']",
                    message:
                        'Do not define inline form schema arrays in page/components. Move form schemas to `resources/js/modules/**/forms/*-form-schema.ts` and consume via builder functions.'
                },
                {
                    selector:
                        "VariableDeclarator[id.name=/^(sidebarNavItems|dashboardPrimaryItems|dashboardFooterItems|marketingPrimaryItems|marketingFooterGroups)$/][init.type='ArrayExpression']",
                    message: 'Do not duplicate navigation arrays. Centralize navigation contracts in `resources/js/config/navigation.ts`.'
                }
            ],
            'no-restricted-imports': [
                'error',
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
