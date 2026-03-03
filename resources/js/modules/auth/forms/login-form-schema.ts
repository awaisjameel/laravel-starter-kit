import { defineFormFields } from '@/types/base-ui'

export interface LoginFormValues {
    email: string
    password: string
    remember: boolean
}

export const buildLoginFormFields = () =>
    defineFormFields<LoginFormValues>([
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
            autocomplete: 'current-password',
            placeholder: 'Password'
        },
        {
            name: 'remember',
            label: 'Remember me',
            type: 'checkbox',
            placeholder: 'Remember me'
        }
    ])
