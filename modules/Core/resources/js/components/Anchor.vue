<template>
  <component :is="as" :id="'hook-' + name" v-bind="$attrs" />

  <template v-for="(item, index) in sortedTeleports" :key="index">
    <Teleport :to="'#hook-' + name">
      <component :is="item.component" v-bind="params" />
    </Teleport>
  </template>
</template>

<script setup>
import { computed, onMounted, shallowRef } from 'vue'

defineOptions({ inheritAttrs: false })

const props = defineProps({
  as: { type: String, default: 'div' },
  name: { type: String, required: true },
  params: Object,
})

const anchorTeleports = shallowRef([])

const sortedTeleports = computed(() => {
  return anchorTeleports.value.slice().sort((a, b) => a.priority - b.priority)
})

onMounted(() => {
  anchorTeleports.value = Innoclapps.teleport[props.name] || []
})
</script>
