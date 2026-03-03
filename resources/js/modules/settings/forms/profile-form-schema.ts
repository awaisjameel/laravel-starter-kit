import { defineFormFields } from '@/types/base-ui'

export interface ProfileFormValues {
    name: string
    email: string
}

export const buildProfileFormFields = () =>
    defineFormFields<ProfileFormValues>([
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
            autocomplete: 'username',
            placeholder: 'Email address'
        }
    ])
