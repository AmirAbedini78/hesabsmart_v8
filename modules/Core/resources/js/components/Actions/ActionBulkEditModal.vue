<template>
  <IModal
    :title="action.name"
    :ok-text="action.confirmButtonText"
    :ok-disabled="busy"
    :cancel-text="action.cancelButtonText"
    :size="action.size"
    hide-header-close
    static
    form
    @submit="emitConfirmationEvent"
  >
    <div class="flex flex-col">
      <FieldsButtonCollapse
        v-if="totalCollapsable > 0"
        v-model:collapsed="fieldsCollapsed"
        class="mb-3 ml-auto"
        :total="totalCollapsable"
      />

      <FormFields
        :fields="fields"
        :collapsed="fieldsCollapsed"
        :form="form"
        is-floating
        @update-field-value="$emit('updateFieldValue', $event)"
        @set-initial-value="$emit('setFieldInitialValue', $event)"
      >
        <template
          v-for="field in fields"
          #[field.beforeFieldSlotName]
          :key="field.attribute"
        >
          <div
            v-if="field.attribute"
            :class="[
              field.displayNone || (field.collapsed && fieldsCollapsed)
                ? 'hidden'
                : '',
              fieldsBeingUpdated[field.attribute] === replaceKey ? '-mb-2' : '',
            ]"
          >
            <div class="mb-3">
              <IFormLabel
                class="mb-1"
                :for="field.attribute"
                :required="
                  fieldsBeingUpdated[field.attribute] === replaceKey &&
                  field.isRequired
                "
                :label="field.label"
              />

              <ICustomSelect
                v-model="fieldsBeingUpdated[field.attribute]"
                :reduce="option => option.value"
                :input-id="field.attribute"
                :clearable="false"
                :options="[
                  {
                    value: keepKey,
                    label: $t('core::fields.keep_existing_value'),
                  },
                  {
                    value: replaceKey,
                    label: $t('core::fields.replace_existing_value'),
                  },
                ]"
              />
            </div>
          </div>
        </template>
      </FormFields>
    </div>
  </IModal>
</template>

<script setup>
import { reactive, ref, watch } from 'vue'

import { useResourceFields } from '@/Core/composables/useResourceFields'

const props = defineProps({
  action: { type: Object, required: true },
  form: { type: Object, required: true },
  ids: { type: Array, required: true },
  busy: { type: Boolean, required: true },
  resourceName: { type: String, required: true },
})

const emit = defineEmits([
  'confirm',
  'updateFieldValue',
  'setFieldInitialValue',
])

const keepKey = 'keep'
const replaceKey = 'replace'
const fieldsCollapsed = ref(true)
const fieldsBeingUpdated = reactive({})

const { fields, updateField, totalCollapsable } = useResourceFields(
  props.action.fields
)

prepareFieldsForBulkEdit()

watch(
  fieldsBeingUpdated,
  newVal => {
    Object.entries(newVal).forEach(([attribute, value]) => {
      updateField(attribute, { hidden: value === keepKey })
    })
  },
  {
    deep: true,
  }
)

function prepareFieldsForBulkEdit() {
  fields.value.forEach(field => {
    // field.collapsed = false
    field.hidden = true
    field.hideLabel = true
    field.toggleable = false
    field.value = null // no default value for bulk edit fields
    field.beforeFieldSlotName = 'before-' + field.attribute + '-field'

    if (field.attribute) {
      fieldsBeingUpdated[field.attribute] = keepKey
    }
  })
}

function showHiddenFieldsWithErrors() {
  fields.value.forEach(field => {
    if (
      props.form.errors.has(field.attribute) &&
      fieldsBeingUpdated[field.attribute] === keepKey
    ) {
      fieldsBeingUpdated[field.attribute] = replaceKey
    }
  })
}

function emitConfirmationEvent() {
  emit('confirm', {
    onSubmit: form => {
      Object.entries(fieldsBeingUpdated).forEach(([attribute, value]) => {
        if (value === keepKey) {
          delete form[attribute]
        }
      })
    },
    onError: () => {
      // Show any fields that the user choosed to keep
      // but there are validation errors related to them.
      showHiddenFieldsWithErrors()
    },
  })
}
</script>
