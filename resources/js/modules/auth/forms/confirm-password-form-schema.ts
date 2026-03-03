import { defineFormFields } from '@/types/base-ui'

export interface ConfirmPasswordFormValues {
    password: string
}

export const buildConfirmPasswordFormFields = () =>
    defineFormFields<ConfirmPasswordFormValues>([
        {
            name: 'password',
            label: 'Password',
            type: 'password',
            required: true,
            autocomplete: 'current-password',
            placeholder: 'Password'
        }
    ])
