<template>
  <ICustomSelect
    v-model="selectedAction"
    label="name"
    :clearable="false"
    :filterable="false"
    :options="actions"
    :placeholder="$t('core::actions.select')"
    @option-selected="handleSelectionChange"
  />
</template>

<script setup>
import { ref } from 'vue'

defineProps({
  actions: { type: Array, default: () => [] },
})

const emit = defineEmits(['change'])

const selectedAction = ref(null)

function handleSelectionChange(action) {
  emit('change', action)
  selectedAction.value = null
}
</script>
