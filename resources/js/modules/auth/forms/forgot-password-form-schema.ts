import { defineFormFields } from '@/types/base-ui'

export interface ForgotPasswordFormValues {
    email: string
}

export const forgotPasswordFormContract = defineFormContract<ForgotPasswordFormValues>({
    defaults: () => ({
        email: ''
    }),
    fields: () =>
        defineFormFields<ForgotPasswordFormValues>([
            {
                name: 'email',
                label: 'Email address',
                type: 'email',
                required: true,
                placeholder: 'email@example.com'
            }
        ])
})

export const createForgotPasswordFormDefaults = forgotPasswordFormContract.defaults
export const buildForgotPasswordFormFields = forgotPasswordFormContract.fields
