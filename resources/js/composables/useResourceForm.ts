import type { InertiaRouteDefinition } from '@/utils/route'
import { toInertiaRouteDefinition } from '@/utils/route'
import type { RouteDefinition } from '@/wayfinder'
import type { FormDataType } from '@inertiajs/core'

type FormValues<T extends object> = FormDataType<T>

export interface ResourceSubmitOptions<TForm extends object> {
    preserveScroll?: boolean
    preserveState?: boolean
    resetOnSuccess?: true | Array<keyof TForm & string>
    onSuccess?: () => void
    onError?: (errors: Partial<Record<keyof TForm & string, string>>) => void
    onFinish?: () => void
}

export function useResourceForm<TForm extends FormValues<TForm>>(initialValues: TForm) {
    const form = useForm(initialValues)

    const resetByPolicy = (resetOnSuccess?: true | Array<keyof TForm & string>): void => {
        if (resetOnSuccess === true) {
            form.reset()
            return
        }

        if (Array.isArray(resetOnSuccess) && resetOnSuccess.length > 0) {
            resetOnSuccess.forEach((field) => {
                form.reset(field as never)
            })
        }
    }

    const submit = (
        definition: RouteDefinition<'get' | 'post' | 'put' | 'patch' | 'delete'> | InertiaRouteDefinition,
        options: ResourceSubmitOptions<TForm> = {}
    ): void => {
        const routeDefinition = toInertiaRouteDefinition(definition)

        form.submit(routeDefinition.method, routeDefinition.url, {
            preserveScroll: options.preserveScroll ?? true,
            preserveState: options.preserveState ?? true,
            onSuccess: () => {
                resetByPolicy(options.resetOnSuccess)
                options.onSuccess?.()
            },
            onError: (errors) => {
                options.onError?.(errors as Partial<Record<keyof TForm & string, string>>)
            },
            onFinish: () => {
                options.onFinish?.()
            }
        })
    }

    return {
        form,
        submit
    }
}
