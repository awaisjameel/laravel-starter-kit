import type { AppPageProps } from '@/types'

export function useFlashToasts() {
    const page = usePage<AppPageProps>()
    const { success, error, info } = useToast()

    const lastSeen = ref({
        message: '',
        error: '',
        status: ''
    })

    watch(
        () => page.props.flash,
        (flash) => {
            const message = flash.message ?? ''
            const messageError = flash.error ?? ''
            const status = flash.status ?? ''

            if (message !== '' && message !== lastSeen.value.message) {
                success({ title: message })
            }

            if (messageError !== '' && messageError !== lastSeen.value.error) {
                error({ title: messageError })
            }

            if (status !== '' && status !== lastSeen.value.status) {
                info({ title: status })
            }

            lastSeen.value = {
                message,
                error: messageError,
                status
            }
        },
        { deep: true, immediate: true }
    )
}
