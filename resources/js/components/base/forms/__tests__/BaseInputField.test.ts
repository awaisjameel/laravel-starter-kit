import { mount } from '@vue/test-utils'
import { expect, it } from 'vitest'
import BaseInputField from '../BaseInputField.vue'

it('renders one checkbox label and preserves its required marker and interaction', async () => {
    const wrapper = mount(BaseInputField<{ remember: boolean }>, {
        props: {
            id: 'remember',
            field: { name: 'remember', label: 'Remember me', type: 'checkbox', required: true },
            modelValue: false
        }
    })
    try {
        expect(wrapper.findAll('label[for="remember"]')).toHaveLength(1)
        expect(wrapper.get('label').text()).toBe('Remember me *')
        await wrapper.get('[role="checkbox"]').trigger('click')
        expect(wrapper.emitted('update:modelValue')).toEqual([[true]])
    } finally {
        wrapper.unmount()
    }
})

it('associates a select label with its focusable trigger', () => {
    const wrapper = mount(BaseInputField<{ role: string }>, {
        props: {
            id: 'role',
            field: { name: 'role', label: 'Role', type: 'select', options: [{ value: 'user', label: 'User' }] },
            modelValue: 'user'
        }
    })
    try {
        expect(wrapper.get('[role="combobox"]').attributes('id')).toBe('role')
        expect(wrapper.get('label').attributes('for')).toBe('role')
    } finally {
        wrapper.unmount()
    }
})
