#!/usr/bin/env node
import { mkdir, stat, writeFile } from 'node:fs/promises'
import path from 'node:path'
import { fileURLToPath } from 'node:url'

const __filename = fileURLToPath(import.meta.url)
const __dirname = path.dirname(__filename)
const projectRoot = path.resolve(__dirname, '..')

const parseArgs = (argv) => {
    const parsed = {}

    argv.forEach((argument) => {
        if (!argument.startsWith('--')) {
            return
        }

        const [rawKey, ...valueParts] = argument.slice(2).split('=')
        const key = rawKey.trim()
        const value = valueParts.join('=').trim()

        if (key !== '') {
            parsed[key] = value
        }
    })

    return parsed
}

const toPascalCase = (value) =>
    value
        .replace(/([a-z0-9])([A-Z])/g, '$1 $2')
        .split(/[^a-zA-Z0-9]+/)
        .filter((part) => part !== '')
        .map((part) => part.charAt(0).toUpperCase() + part.slice(1).toLowerCase())
        .join('')

const toKebabCase = (value) =>
    value
        .replace(/([a-z0-9])([A-Z])/g, '$1-$2')
        .replace(/[^a-zA-Z0-9]+/g, '-')
        .replace(/--+/g, '-')
        .replace(/^-|-$/g, '')
        .toLowerCase()

const toCamelCase = (value) => value.charAt(0).toLowerCase() + value.slice(1)

const ensureDirectory = async (directoryPath) => {
    await mkdir(directoryPath, { recursive: true })
}

const assertFileDoesNotExist = async (filePath) => {
    try {
        await stat(filePath)
        throw new Error(`File already exists: ${filePath}`)
    } catch (error) {
        if (error instanceof Error && 'code' in error && error.code === 'ENOENT') {
            return
        }

        throw error
    }
}

const writeNewFile = async (filePath, content) => {
    await assertFileDoesNotExist(filePath)
    await writeFile(filePath, `${content}\n`, 'utf8')
}

const printUsage = () => {
    console.error('Usage: npm run generate:frontend-page -- --module=<module-name> --page=<page-name>')
}

const args = parseArgs(process.argv.slice(2))
const rawModuleName = args.module
const rawPageName = args.page

if (rawModuleName === undefined || rawModuleName === '' || rawPageName === undefined || rawPageName === '') {
    printUsage()
    process.exit(1)
}

const moduleName = toKebabCase(rawModuleName)
const pagePascalName = toPascalCase(rawPageName)
const pageCamelName = toCamelCase(pagePascalName)
const pageKebabName = toKebabCase(rawPageName)

if (pagePascalName === '' || pageKebabName === '') {
    console.error('Could not derive valid page names from the provided --page value.')
    process.exit(1)
}

const moduleRoot = path.join(projectRoot, 'resources', 'js', 'modules', moduleName)
const formsDirectory = path.join(moduleRoot, 'forms')
const pagesDirectory = path.join(moduleRoot, 'pages')
const pageTestsDirectory = path.join(pagesDirectory, '__tests__')
const routesDirectory = path.join(moduleRoot, 'routes')

const schemaFilePath = path.join(formsDirectory, `${pageKebabName}-form-schema.ts`)
const pageFilePath = path.join(pagesDirectory, `${pagePascalName}.vue`)
const routeContractFilePath = path.join(routesDirectory, `${pageKebabName}-route-contract.ts`)
const pageTestFilePath = path.join(pageTestsDirectory, `${pagePascalName}.test.ts`)

const schemaContent = `import { defineFormFields } from '@/types/base-ui'

export interface ${pagePascalName}FormValues {
    name: string
}

export const ${pageCamelName}FormContract = defineFormContract<${pagePascalName}FormValues>({
    defaults: () => ({
        name: ''
    }),
    fields: () =>
        defineFormFields<${pagePascalName}FormValues>([
            {
                name: 'name',
                label: 'Name',
                type: 'text',
                required: true,
                placeholder: 'Enter name'
            }
        ])
})

export const create${pagePascalName}FormDefaults = ${pageCamelName}FormContract.defaults
export const build${pagePascalName}FormFields = ${pageCamelName}FormContract.fields`

const routeContractContent = `import type { InertiaRouteDefinition } from '@/utils/route'

export interface ${pagePascalName}RouteContract {
    submit: InertiaRouteDefinition
}

export const define${pagePascalName}RouteContract = (contract: ${pagePascalName}RouteContract): ${pagePascalName}RouteContract => contract`

const pageContent = `<script setup lang="ts">
    import { ${pageCamelName}FormContract, type ${pagePascalName}FormValues } from '../forms/${pageKebabName}-form-schema'
    import { type ${pagePascalName}RouteContract } from '../routes/${pageKebabName}-route-contract'

    interface Props {
        routeContract: ${pagePascalName}RouteContract
    }

    const props = defineProps<Props>()

    const { form, fields, submit } = useSchemaResourceForm<${pagePascalName}FormValues>(${pageCamelName}FormContract)

    const submitForm = (): void => {
        submit(props.routeContract.submit)
    }
</script>

<template>
    <BaseFormsBaseFormRenderer
        :model="form"
        :fields="fields"
        :errors="form.errors"
        :processing="form.processing"
        submit-label="Save"
        @submit="submitForm"
    />
</template>`

const pageTestContent = `import { describe, expect, it } from 'vitest'
import { create${pagePascalName}FormDefaults, build${pagePascalName}FormFields } from '../../forms/${pageKebabName}-form-schema'
import { define${pagePascalName}RouteContract } from '../../routes/${pageKebabName}-route-contract'

describe('${pagePascalName} generator scaffolding', () => {
    it('exposes typed defaults and fields', () => {
        const defaults = create${pagePascalName}FormDefaults()
        const fields = build${pagePascalName}FormFields()

        expect(defaults).toEqual({ name: '' })
        expect(fields[0]?.name).toBe('name')
    })

    it('builds a typed route contract', () => {
        const contract = define${pagePascalName}RouteContract({
            submit: {
                method: 'post',
                url: '/example'
            }
        })

        expect(contract.submit.url).toBe('/example')
    })
})`

await ensureDirectory(formsDirectory)
await ensureDirectory(pagesDirectory)
await ensureDirectory(pageTestsDirectory)
await ensureDirectory(routesDirectory)

await writeNewFile(schemaFilePath, schemaContent)
await writeNewFile(pageFilePath, pageContent)
await writeNewFile(routeContractFilePath, routeContractContent)
await writeNewFile(pageTestFilePath, pageTestContent)

console.log('Generated frontend scaffolding:')
console.log(`- ${path.relative(projectRoot, schemaFilePath)}`)
console.log(`- ${path.relative(projectRoot, pageFilePath)}`)
console.log(`- ${path.relative(projectRoot, routeContractFilePath)}`)
console.log(`- ${path.relative(projectRoot, pageTestFilePath)}`)
