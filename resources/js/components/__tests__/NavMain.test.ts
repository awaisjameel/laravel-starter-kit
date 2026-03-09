import { mount } from '@vue/test-utils'
import { describe, expect, it } from 'vitest'
import { defineComponent, h } from 'vue'
import NavMain from '../NavMain.vue'

const passthroughStub = defineComponent({
    setup(_, { slots }) {
        return () => h('div', slots.default?.())
    }
})

const menuButtonStub = defineComponent({
    inheritAttrs: false,
    setup(_, { attrs, slots }) {
        return () => h('button', attrs, slots.default?.())
    }
})

const linkStub = defineComponent({
    props: {
        href: {
            type: String,
            required: true
        }
    },
    inheritAttrs: false,
    setup(props, { attrs, slots }) {
        return () => h('a', { href: props.href, ...attrs }, slots.default?.())
    }
})

describe('NavMain', () => {
    it('uses centralized item isActive state for aria-current', () => {
        const wrapper = mount(NavMain, {
            props: {
                items: [
                    {
                        title: 'Dashboard',
                        href: '/app/dashboard',
                        isActive: true
                    },
                    {
                        title: 'Users',
                        href: '/app/admin/users',
                        isActive: false
                    }
                ]
            },
            global: {
                stubs: {
                    UiSidebarGroup: passthroughStub,
                    UiSidebarGroupLabel: passthroughStub,
                    UiSidebarMenu: passthroughStub,
                    UiSidebarMenuItem: passthroughStub,
                    UiSidebarMenuButton: menuButtonStub,
                    Link: linkStub
                }
            }
        })

        const links = wrapper.findAll('a')

        expect(links).toHaveLength(2)
        const firstLink = links[0]
        const secondLink = links[1]

        expect(firstLink).toBeDefined()
        expect(secondLink).toBeDefined()
        expect(firstLink?.attributes('aria-current')).toBe('page')
        expect(secondLink?.attributes('aria-current')).toBeUndefined()
    })
})
