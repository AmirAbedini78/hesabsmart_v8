<!-- eslint-disable vue/multi-word-component-names -->
<template>
  <ICard>
    <div
      ref="wrapperRef"
      :class="[isResizeable ? 'resize-y overflow-y-auto scrollbar-thin' : '']"
      :style="{
        height: disableResize ? undefined : height,
      }"
    >
      <div class="px-5 py-4">
        <slot />
      </div>
    </div>
  </ICard>
</template>

<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref } from 'vue'
import elementResizeEvent from 'element-resize-event'
import { unbind as unbindElementResizeEvent } from 'element-resize-event'

import { useGate } from '@/Core/composables/useGate'
import { debounce } from '@/Core/utils'

import { useApp } from '../composables/useApp'

const props = defineProps({
  panel: { type: Object, required: true },
  disableResize: Boolean,
})

const { gate } = useGate()
const { scriptConfig } = useApp()

const wrapperRef = ref(null)

const configKey = computed(() => `${props.panel.id}_panel_height`)
const runtimeConfigKey = computed(() => '_runtime_' + configKey.value)

const height = ref(scriptConfig(runtimeConfigKey.value) || props.panel.height)

const isResizeable = computed(
  () => props.panel.resizeable && !props.disableResize && gate.isSuperAdmin()
)

const updateHeight = debounce(async function () {
  if (props.disableResize || !isResizeable.value) {
    return
  }

  let elementHeight = `${wrapperRef.value.offsetHeight}px`

  await Innoclapps.request().post('/settings', {
    [configKey.value]: elementHeight,
  })

  scriptConfig(runtimeConfigKey.value, elementHeight)

  height.value = elementHeight
}, 500)

function createResizableEvent() {
  elementResizeEvent(wrapperRef.value, updateHeight)
}

function destroyResizableEvent() {
  if (props.panel.resizeable) {
    unbindElementResizeEvent(wrapperRef.value)
  }
}

function prepareComponent() {
  if (props.panel.resizeable) {
    nextTick(createResizableEvent)
  }
}

onMounted(prepareComponent)

onBeforeUnmount(destroyResizableEvent)
</script>
