import { defineFormFields } from '@/types/base-ui'

export interface DeleteAccountFormValues {
    password: string
}

export const deleteAccountFormContract = defineFormContract<DeleteAccountFormValues>({
    defaults: () => ({
        password: ''
    }),
    fields: () =>
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
})

export const createDeleteAccountFormDefaults = deleteAccountFormContract.defaults
export const buildDeleteAccountFormFields = deleteAccountFormContract.fields
