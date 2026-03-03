import { defineFormFields } from '@/types/base-ui'

export interface ForgotPasswordFormValues {
    email: string
}

export const buildForgotPasswordFormFields = () =>
    defineFormFields<ForgotPasswordFormValues>([
        {
            name: 'email',
            label: 'Email address',
            type: 'email',
            required: true,
            placeholder: 'email@example.com'
        }
    ])
