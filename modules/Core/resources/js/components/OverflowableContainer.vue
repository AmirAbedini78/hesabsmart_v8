<script setup>
import { onMounted, onUnmounted, ref, watchEffect } from 'vue'

defineOptions({
  inheritAttrs: false,
})

defineProps({
  tag: {
    type: String,
    default: 'div',
  },
})

const containerRef = ref(null)
const isOverflowing = ref(false)

const checkOverflow = () => {
  if (containerRef.value) {
    isOverflowing.value =
      containerRef.value.scrollWidth > containerRef.value.clientWidth
  }
}

onMounted(() => {
  checkOverflow()
  window.addEventListener('resize', checkOverflow)
})

onUnmounted(() => {
  window.removeEventListener('resize', checkOverflow)
})

watchEffect(() => {
  checkOverflow()
})
</script>

<template>
  <component
    :is="tag"
    ref="containerRef"
    :style="{ overflow: 'hidden' }"
    :data-overflows="isOverflowing ? '' : undefined"
    v-bind="$attrs"
  >
    <slot />
  </component>
</template>
