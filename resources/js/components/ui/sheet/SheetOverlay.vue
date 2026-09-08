<script setup lang="ts">
import { cn } from '@/lib/utils'
import { appTheme } from '@/lib/theme'
import { DialogOverlay, type DialogOverlayProps } from 'reka-ui'
import { computed, type HTMLAttributes } from 'vue'

const props = defineProps<DialogOverlayProps & { class?: HTMLAttributes['class'] }>()

const delegatedProps = computed(() => {
  const { class: _, ...delegated } = props

  return delegated as Partial<DialogOverlayProps>
})
</script>

<template>
  <DialogOverlay
    data-slot="sheet-overlay"
    :class="cn(appTheme.overlay, props.class)"
    v-bind="delegatedProps"
  >
    <slot />
  </DialogOverlay>
</template>
