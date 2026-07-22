import type { AppPageProps } from '@/types/index'
import type { createHeadManager, Page, Router } from '@inertiajs/core'

// Extend ImportMeta interface for Vite...
declare module 'vite/client' {
    interface ImportMetaEnv {
        readonly VITE_APP_NAME: string
        readonly VITE_REVERB_APP_KEY: string
        readonly VITE_REVERB_HOST: string
        readonly VITE_REVERB_PORT: string
        readonly VITE_REVERB_SCHEME: 'http' | 'https'
        [key: string]: string | boolean | undefined
    }

    interface ImportMeta {
        readonly env: ImportMetaEnv
        readonly glob: <T>(pattern: string) => Record<string, () => Promise<T>>
    }
}

// Interface merging, so this only has to add what the app contributes: Inertia's
// own `PageProps` members come from the declaration being merged into. Listing
// `PageProps` as a supertype here would resolve to this same augmentation and be
// circular, so the empty body is the point rather than an oversight.
declare module '@inertiajs/core' {
    // eslint-disable-next-line @typescript-eslint/no-empty-object-type
    interface PageProps extends AppPageProps {}
}

declare module '@vue/runtime-core' {
    interface ComponentCustomProperties {
        $inertia: Router
        $page: Page
        $headManager: ReturnType<typeof createHeadManager>
    }
}
