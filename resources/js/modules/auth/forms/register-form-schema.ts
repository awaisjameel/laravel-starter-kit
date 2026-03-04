import { defineFormFields } from '@/types/base-ui'

export interface RegisterFormValues {
    name: string
    email: string
    password: string
    password_confirmation: string
}

export const registerFormContract = defineFormContract<RegisterFormValues>({
    defaults: () => ({
        name: '',
        email: '',
        password: '',
        password_confirmation: ''
    }),
    fields: () =>
        defineFormFields<RegisterFormValues>([
            {
                name: 'name',
                label: 'Name',
                type: 'text',
                required: true,
                autocomplete: 'name',
                placeholder: 'Full name'
            },
            {
                name: 'email',
                label: 'Email address',
                type: 'email',
                required: true,
                autocomplete: 'email',
                placeholder: 'email@example.com'
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
                label: 'Confirm password',
                type: 'password',
                required: true,
                autocomplete: 'new-password',
                placeholder: 'Confirm password'
            }
        ])
})

export const createRegisterFormDefaults = registerFormContract.defaults
export const buildRegisterFormFields = registerFormContract.fields
