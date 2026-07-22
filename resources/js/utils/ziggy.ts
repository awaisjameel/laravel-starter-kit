import type { Config } from 'ziggy-js'

type ZiggyConfigWithLocation = Config & {
    location: string | URL
}

export const toZiggyVueConfig = (ziggy: ZiggyConfigWithLocation): Config & { location: URL } => ({
    ...ziggy,
    location: ziggy.location instanceof URL ? ziggy.location : new URL(ziggy.location)
})
