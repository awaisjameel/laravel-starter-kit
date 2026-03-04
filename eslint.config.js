import prettier from 'eslint-config-prettier'
import vue from 'eslint-plugin-vue'

import { autoImportRestrictedPaths, autoImportRestrictedPatterns } from './frontend-auto-import.config.mjs'
import { defineConfigWithVueTs, vueTsConfigs } from '@vue/eslint-config-typescript'

const frontendModuleNames = ['auth', 'dashboard', 'marketing', 'settings', 'users']

const baseRestrictedSyntaxSelectors = [
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
]

const buildRestrictedSyntaxRule = (extraSelectors = []) => ['error', ...baseRestrictedSyntaxSelectors, ...extraSelectors]

const buildNoRestrictedImportsRule = (extraPatterns = []) => [
    'error',
    {
        paths: autoImportRestrictedPaths,
        patterns: [...autoImportRestrictedPatterns, ...extraPatterns]
    }
]

const moduleBoundaryConfigs = frontendModuleNames.map((moduleName) => ({
    files: [`resources/js/modules/${moduleName}/**/*.{ts,vue}`],
    rules: {
        'no-restricted-imports': buildNoRestrictedImportsRule([
            {
                group: frontendModuleNames.filter((candidate) => candidate !== moduleName).map((candidate) => `@/modules/${candidate}/**`),
                message: `Cross-module imports are not allowed in feature module "${moduleName}". Move shared code to a shared/base layer.`
            }
        ])
    }
}))

const pageDataAccessGuardConfig = {
    files: ['resources/js/modules/**/pages/**/*.{ts,vue}'],
    rules: {
        'no-restricted-syntax': buildRestrictedSyntaxRule([
            {
                selector: "CallExpression[callee.name='fetch']",
                message:
                    'Do not call fetch directly in page files. Use shared API/query composables (`useApiQuery`, `useApiMutation`) via module contracts.'
            }
        ])
    }
}

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
            'no-restricted-syntax': buildRestrictedSyntaxRule(),
            'no-restricted-imports': buildNoRestrictedImportsRule()
        }
    },
    ...moduleBoundaryConfigs,
    pageDataAccessGuardConfig,
    prettier
)
