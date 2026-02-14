<template>
  <component
    :is="type === 'select' ? ActionSelectorSelect : ActionSelectorDropdown"
    v-if="actionsForCurrentView.length > 0"
    :actions="actionsForCurrentView"
    v-bind="$attrs"
    @change="handleSelectionChange"
  />

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
import castArray from 'lodash/castArray'

import { useAction } from '@/Core/composables/useAction'

import ActionSelectorDropdown from './ActionSelectorDropdown.vue'
import ActionSelectorSelect from './ActionSelectorSelect.vue'

defineOptions({ inheritAttrs: false })

const props = defineProps({
  ids: { type: [Number, String, Array], required: true },
  resourceName: { type: String, required: true },
  actions: { type: Array, default: () => [] },
  additionalRequestParams: { type: Object, default: () => ({}) },
  type: {
    required: true,
    type: String,
    validator: value => ['select', 'dropdown'].includes(value),
  },
  view: {
    default: 'detail',
    validator: value => ['index', 'detail'].indexOf(value) !== -1,
  },
})

const emit = defineEmits(['actionExecuted'])

const selectedIds = computed(() => castArray(props.ids))

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

const applicableActions = computed(() =>
  props.actions.filter(
    action => !action.sole || (action.sole && selectedIds.value.length === 1)
  )
)

const actionsForCurrentView = computed(() => {
  return applicableActions.value.filter(action => {
    return (
      (props.view === 'detail' && action.showOnDetail === true) ||
      (props.view === 'index' && action.showOnIndex === true)
    )
  })
})
</script>
