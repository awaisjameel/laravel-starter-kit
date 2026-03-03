import { defineFormFields } from '@/types/base-ui'

export interface ResetPasswordFormValues {
    token: string
    email: string
    password: string
    password_confirmation: string
}

export const buildResetPasswordFormFields = () =>
    defineFormFields<ResetPasswordFormValues>([
        {
            name: 'email',
            label: 'Email',
            type: 'email',
            readonly: true
        },
        {
            name: 'password',
            label: 'Password',
            type: 'password',
            required: true,
            autocomplete: 'new-password',
            placeholder: 'Password'
        },
        {
            name: 'password_confirmation',
            label: 'Confirm Password',
            type: 'password',
            required: true,
            autocomplete: 'new-password',
            placeholder: 'Confirm password'
        }
    ])
