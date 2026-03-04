import { defineFormFields } from '@/types/base-ui'

export interface ConfirmPasswordFormValues {
    password: string
}

export const confirmPasswordFormContract = defineFormContract<ConfirmPasswordFormValues>({
    defaults: () => ({
        password: ''
    }),
    fields: () =>
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
})

export const createConfirmPasswordFormDefaults = confirmPasswordFormContract.defaults
export const buildConfirmPasswordFormFields = confirmPasswordFormContract.fields
