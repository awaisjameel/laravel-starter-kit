import { readFileSync, readdirSync, statSync } from 'node:fs'
import { join, relative } from 'node:path'
import { describe, expect, it } from 'vitest'

import { appTheme, buttonStyles, sidebarButtonStyles, toastStyles } from '../theme'

const projectRoot = process.cwd()

const sourceFiles = (directory: string): string[] =>
    readdirSync(directory).flatMap((entry) => {
        const path = join(directory, entry)

        if (statSync(path).isDirectory()) {
            return entry === '__tests__' ? [] : sourceFiles(path)
        }

        return /\.(css|ts|vue)$/.test(entry) ? [path] : []
    })

describe('theme contracts', () => {
    it('resolves typed control variants from the canonical recipes', () => {
        expect(buttonStyles()).toContain(appTheme.button.variant.default)
        expect(buttonStyles({ variant: 'destructive', size: 'sm' })).toContain(appTheme.button.variant.destructive)
        expect(sidebarButtonStyles({ variant: 'outline', size: 'lg' })).toContain(appTheme.navigation.sidebarVariant.outline)
        expect(toastStyles('success')).toContain(appTheme.toast.variant.success)
    })

    it('keeps raw visual values in theme.css', () => {
        const themePath = join(projectRoot, 'resources/css/theme.css')
        const candidates = [...sourceFiles(join(projectRoot, 'resources/js')), ...sourceFiles(join(projectRoot, 'resources/css'))].filter(
            (path) => path !== themePath
        )
        const rawColor = /#[\da-f]{3,8}\b|\b(?:rgb|hsl)a?\(/i
        const paletteUtility =
            /\b(?:bg|border|fill|ring|stroke|text)-(?:slate|gray|zinc|neutral|stone|red|orange|amber|yellow|lime|green|emerald|teal|cyan|sky|blue|indigo|violet|purple|fuchsia|pink|rose|white|black)(?:-|\b)/
        const paletteToken =
            /--color-(?:slate|gray|zinc|neutral|stone|red|orange|amber|yellow|lime|green|emerald|teal|cyan|sky|blue|indigo|violet|purple|fuchsia|pink|rose|white|black)(?:-|\b)/
        const violations = candidates
            .filter((path) => {
                const contents = readFileSync(path, 'utf8')

                return rawColor.test(contents) || paletteUtility.test(contents) || paletteToken.test(contents)
            })
            .map((path) => relative(projectRoot, path))

        expect(violations).toEqual([])
        expect(readFileSync(join(projectRoot, 'resources/css/app.css'), 'utf8')).toContain("@import './theme.css';")
    })

    it('keeps presentation dependencies and local style blocks out of frontend source', () => {
        const candidates = sourceFiles(join(projectRoot, 'resources/js'))
        const bannedDependency = /@lucide\/vue|class-variance-authority/
        const violations = candidates
            .filter((path) => {
                const contents = readFileSync(path, 'utf8')

                return bannedDependency.test(contents) || (path.endsWith('.vue') && contents.includes('<style'))
            })
            .map((path) => relative(projectRoot, path))

        expect(violations).toEqual([])
    })
})
