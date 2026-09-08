// @vitest-environment node
import { createRequire } from 'node:module'
import { expect, it } from 'vitest'

const require = createRequire(import.meta.url)
const runtime = require('../../../../pm2.config.cjs') as {
    apps: Array<{ name: string; instances: number; autorestart: boolean; stop_exit_codes?: number[]; interpreter_args: string }>
}

it('runs one scheduler and restarts long-lived services after a graceful deployment exit', () => {
    expect(runtime.apps.find((app) => app.name === 'scheduler')?.instances).toBe(1)
    for (const app of runtime.apps) {
        expect(app.autorestart).toBe(true)
        expect(app.stop_exit_codes ?? []).not.toContain(0)
    }
    expect(runtime.apps.find((app) => app.name === 'queue-workers')?.interpreter_args).toContain('--queue=realtime,high,default')
})
