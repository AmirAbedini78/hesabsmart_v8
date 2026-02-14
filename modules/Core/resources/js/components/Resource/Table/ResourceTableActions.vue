<template>
  <div
    v-show="ids.length > 0"
    ref="wrapperRef"
    class="absolute top-0 z-50 h-11 w-full bg-neutral-50/60 dark:bg-neutral-500/20"
  >
    <div
      ref="innerRef"
      class="h-full border-x border-neutral-900/10 bg-neutral-50 px-1 dark:border-white/10 dark:bg-neutral-900 dark:before:absolute dark:before:inset-0 dark:before:bg-neutral-500/10 sm:pt-1 [&_.toggle-icon]:size-5"
    >
      <ActionSelector
        class="z-50 !bg-transparent !shadow-none !ring-0"
        type="select"
        view="index"
        toggle-icon="PlayCircle"
        placeholder-type="display"
        :ids="ids"
        :placeholder="`${$t('core::actions.select')} (${$t(
          'core::actions.records_count',
          {
            count: ids.length,
          }
        )})`"
        :additional-request-params="runActionRequestAdditionalParams"
        :actions="actions || []"
        :resource-name="resourceName"
        @action-executed="$emit('actionExecuted', $event)"
      />
    </div>
  </div>
</template>

<script setup>
import { onUpdated, ref } from 'vue'
import { useParentElement } from '@vueuse/core'

import ActionSelector from '@/Core/components/Actions/ActionSelector.vue'

const props = defineProps([
  'ids',
  'actions',
  'runActionRequestAdditionalParams',
  'resourceName',
])

defineEmits(['actionExecuted'])

const parentEl = useParentElement()

const wrapperRef = ref(null)
const innerRef = ref(null)

function updateWidth() {
  if (props.ids.length === 0) {
    return
  }

  let firstColumnEl = parentEl.value.querySelector('thead>tr>th:first-child')

  let checkboxSeparator = firstColumnEl.querySelector(
    '[data-slot="checkbox-separator"]'
  )

  wrapperRef.value.style.left = checkboxSeparator.offsetLeft + 'px'

  innerRef.value.style.maxWidth =
    firstColumnEl.offsetWidth - checkboxSeparator.offsetLeft + 'px'
}

onUpdated(updateWidth)
</script>
