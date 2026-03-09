<script setup lang="ts">
import type { AvatarImageProps } from 'reka-ui'
import { AvatarImage } from 'reka-ui'
import { computed } from 'vue'

const props = defineProps<AvatarImageProps>()

const forwardedProps = computed(() => {
  const { src: _, ...delegated } = props

  return delegated as Record<string, unknown>
})
</script>

<template>
  <AvatarImage
    data-slot="avatar-image"
    :src="props.src"
    v-bind="forwardedProps"
    class="aspect-square size-full"
  >
    <slot />
  </AvatarImage>
</template>
