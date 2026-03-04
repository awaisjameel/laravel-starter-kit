import { defineFormFields } from '@/types/base-ui'

export interface PasswordFormValues {
    current_password: string
    password: string
    password_confirmation: string
}

export const passwordFormContract = defineFormContract<PasswordFormValues>({
    defaults: () => ({
        current_password: '',
        password: '',
        password_confirmation: ''
    }),
    fields: () =>
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
})

export const createPasswordFormDefaults = passwordFormContract.defaults
export const buildPasswordFormFields = passwordFormContract.fields
