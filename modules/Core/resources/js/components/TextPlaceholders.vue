<template>
  <div
    class="rounded-md border border-neutral-200 bg-neutral-50 p-4 dark:border-neutral-500 dark:bg-neutral-700"
  >
    <div class="max-h-96 overflow-y-auto">
      <ITextDark
        v-for="placeholder in placeholders"
        :key="placeholder.tag"
        class="mb-1"
      >
        <span
          class="select-all font-semibold"
          @dragstart="setDragContent($event, placeholder)"
        >
          {{ placeholder.interpolation_start }}
          {{ placeholder.tag }}
          {{ placeholder.interpolation_end }}
        </span>

        <span v-show="placeholder.description">
          - {{ placeholder.description }}
        </span>
      </ITextDark>
    </div>
  </div>
</template>

<script setup>
defineProps({
  placeholders: Array,
})

function setDragContent(e, placeholder) {
  const content = `${placeholder.interpolation_start}
           ${placeholder.tag}
           ${placeholder.interpolation_end}`

  e.dataTransfer.setData('text/plain', content)
}
</script>
