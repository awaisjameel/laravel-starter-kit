import { AppPageProps } from '@/types/index'

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

declare module '@inertiajs/core' {
    interface PageProps extends InertiaPageProps, AppPageProps {}
}

declare module '@vue/runtime-core' {
    interface ComponentCustomProperties {
        $inertia: typeof Router
        $page: Page
        $headManager: ReturnType<typeof createHeadManager>
    }
}
