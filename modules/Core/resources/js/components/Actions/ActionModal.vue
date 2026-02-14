<template>
  <IModal
    :title="action.name"
    :ok-text="action.confirmButtonText"
    :ok-variant="action.destroyable ? 'danger' : 'primary'"
    :ok-disabled="busy"
    :cancel-text="action.cancelButtonText"
    :sub-title="action.confirmMessage"
    :size="action.size"
    :static="fields > 0"
    hide-header-close
    form
    @submit="$emit('confirm')"
  >
    <IOverlay :show="!componentReady">
      <FormFields
        v-if="componentReady"
        :fields="fields"
        :form="form"
        is-floating
        @update-field-value="$emit('updateFieldValue', $event)"
        @set-initial-value="$emit('setFieldInitialValue', $event)"
      />
    </IOverlay>
  </IModal>
</template>

<script setup>
import { onMounted, ref } from 'vue'

import { useResourceable } from '@/Core/composables/useResourceable'
import { useResourceFields } from '@/Core/composables/useResourceFields'

const props = defineProps({
  action: { type: Object, required: true },
  form: { type: Object, required: true },
  ids: { type: Array, required: true },
  busy: { type: Boolean, required: true },
  resourceName: { type: String, required: true },
})

defineEmits(['confirm', 'updateFieldValue', 'setFieldInitialValue'])

const componentReady = ref(false)

const { retrieveResource } = useResourceable(props.resourceName)
const { fields, updateFieldValue } = useResourceFields(props.action.fields)

async function populateAssociateableField() {
  const record = await retrieveResource(props.ids[0])

  updateFieldValue(props.resourceName, [record])
}

async function prepareComponent() {
  if (props.ids.length === 1 && props.action.fields.length > 0) {
    const resourceHasAssociateableField =
      props.action.fields.filter(
        field => field.attribute === props.resourceName
      ).length > 0

    if (resourceHasAssociateableField) {
      await populateAssociateableField()
    }
  }

  componentReady.value = true
}

onMounted(prepareComponent)
</script>
