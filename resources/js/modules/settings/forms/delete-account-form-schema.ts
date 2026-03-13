import type { FormValuesFromData } from '@/lib/forms'
import type { ProfileDestroyData } from '@/types/app-data'
import { defineFormFields } from '@/types/base-ui'

export type DeleteAccountFormValues = FormValuesFromData<ProfileDestroyData>

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
