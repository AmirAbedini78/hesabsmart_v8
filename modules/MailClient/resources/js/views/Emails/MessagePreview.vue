<template>
  <div v-once class="mail-text all-revert">
    <div
      ref="wrapperRef"
      class="font-sans text-base leading-[initial] sm:text-sm"
    >
      <HtmlableLightbox :html="visibleText" />

      <HiddenText :text="hiddenText" />
    </div>
  </div>
</template>

<script setup>
import { onMounted, onUnmounted, ref } from 'vue'

import HtmlableLightbox from '@/Core/components/Lightbox/HtmlableLightbox.vue'

import HiddenText from './MessageHiddenText.vue'

defineProps({
  visibleText: null,
  hiddenText: null,
})

const wrapperRef = ref(null)
let observer = null

function makeTablesHorizontallyScrollable() {
  const wrapTables = () => {
    const tables = wrapperRef.value.querySelectorAll('table')

    tables.forEach(table => {
      // Ensure the table exceeds its container's width
      if (
        table.offsetWidth > table.parentElement.offsetWidth &&
        !table.parentElement.classList.contains('scrollable-wrapper')
      ) {
        const wrapper = document.createElement('div')
        wrapper.style.overflowX = 'auto'
        wrapper.style.width = '100%'
        wrapper.classList.add('scrollable-wrapper')

        table.parentElement.replaceChild(wrapper, table)
        wrapper.appendChild(table)
      }
    })
  }

  // Observe changes in the DOM
  observer = new MutationObserver(() => wrapTables())
  observer.observe(wrapperRef.value, { childList: true, subtree: true })

  // Initial wrapping
  wrapTables()
}

onUnmounted(() => {
  if (observer) observer.disconnect()
})

onMounted(makeTablesHorizontallyScrollable)
</script>
