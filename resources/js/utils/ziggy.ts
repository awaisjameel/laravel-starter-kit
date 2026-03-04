import type { Config } from 'ziggy-js'
import { route as ziggyRoute } from 'ziggy-js'

type ZiggyConfigWithLocation = Config & {
    location: string | URL
}

export const toZiggyVueConfig = (ziggy: ZiggyConfigWithLocation): Config & { location: URL } => ({
    ...ziggy,
    location: ziggy.location instanceof URL ? ziggy.location : new URL(ziggy.location)
})

export const bindGlobalRouteHelper = (ziggy: ZiggyConfigWithLocation): void => {
    const routeHelper = ((...args: unknown[]) => {
        if (args.length === 0) {
            return ziggyRoute(undefined, undefined, undefined, ziggy)
        }

        return ziggyRoute(
            args[0] as Parameters<typeof ziggyRoute>[0],
            args[1] as Parameters<typeof ziggyRoute>[1],
            args[2] as Parameters<typeof ziggyRoute>[2],
            (args[3] as Parameters<typeof ziggyRoute>[3] | undefined) ?? ziggy
        )
    }) as unknown as typeof ziggyRoute

    ;(globalThis as typeof globalThis & { route: typeof ziggyRoute }).route = routeHelper
}
