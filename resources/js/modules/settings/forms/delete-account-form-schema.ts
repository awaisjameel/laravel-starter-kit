import { defineFormFields } from '@/types/base-ui'

export interface DeleteAccountFormValues {
    password: string
}

export const buildDeleteAccountFormFields = () =>
    defineFormFields<DeleteAccountFormValues>([
        {
            name: 'password',
            label: 'Password',
            type: 'password',
            required: true,
            placeholder: 'Password',
            autocomplete: 'current-password'
        }
    ])
