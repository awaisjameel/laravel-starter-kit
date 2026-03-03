import { defineFormFields } from '@/types/base-ui'

export interface PasswordFormValues {
    current_password: string
    password: string
    password_confirmation: string
}

export const buildPasswordFormFields = () =>
    defineFormFields<PasswordFormValues>([
        {
            name: 'current_password',
            label: 'Current password',
            type: 'password',
            autocomplete: 'current-password',
            placeholder: 'Current password'
        },
        {
            name: 'password',
            label: 'New password',
            type: 'password',
            autocomplete: 'new-password',
            placeholder: 'New password'
        },
        {
            name: 'password_confirmation',
            label: 'Confirm password',
            type: 'password',
            autocomplete: 'new-password',
            placeholder: 'Confirm password'
        }
    ])
