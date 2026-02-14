<template>
  <ITableRowActions v-if="actions.length">
    <ITableRowAction
      v-for="action in actions"
      :key="action.uriKey"
      :text="action.name"
      @click="handleSelectionChange(action)"
    />
  </ITableRowActions>

  <component
    :is="selectedAction.component"
    v-if="confirmationModalVisible"
    v-model:visible="confirmationModalVisible"
    :action="selectedAction"
    :ids="selectedIds"
    :resource-name="resourceName"
    :busy="actionBeingExecuted"
    :form="form"
    @hidden="handleConfirmationModalHidden"
    @confirm="executeAction"
    @update-field-value="form.fill($event.attribute, $event.value)"
    @set-field-initial-value="form.set($event.attribute, $event.value)"
  />
</template>

<script setup>
import { computed } from 'vue'

import { useAction } from '@/Core/composables/useAction'

const props = defineProps({
  actions: { type: Object, required: true },
  resourceName: { type: String, required: true },
  resourceId: { type: Number, required: true },
  additionalRequestParams: { type: Object, required: true },
})

const emit = defineEmits(['actionExecuted'])

const selectedIds = computed(() => [props.resourceId])

const {
  form,
  executeAction,
  selectedAction,
  confirmationModalVisible,
  actionBeingExecuted,
  handleConfirmationModalHidden,
  handleSelectionChange,
} = useAction(props.resourceName, selectedIds, {
  additionalRequestParams: props.additionalRequestParams,
  callback: params => emit('actionExecuted', params),
})
</script>
