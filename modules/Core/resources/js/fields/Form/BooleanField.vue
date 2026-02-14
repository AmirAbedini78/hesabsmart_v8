<template>
  <BaseFormField
    v-slot="{ readonly, fieldId }"
    :resource-name="resourceName"
    :field="field"
    :value="model"
    :is-floating="isFloating"
  >
    <FormFieldGroup
      :field="field"
      :field-id="fieldId"
      :validation-errors="validationErrors"
      as-paragraph-label
    >

      <IFormCheckboxField>
        <IFormCheckbox
          :id="fieldId"
          v-model:checked="model"
          :name="field.attribute"
          :unchecked-value="field.falseValue"
          :value="field.trueValue"
          :disabled="readonly"
          v-bind="field.attributes"
        />

        <IFormCheckboxLabel :for="fieldId">
          <!-- eslint-disable-next-line vue/no-v-html -->
          <span v-html="field.label"></span>
        </IFormCheckboxLabel>

        <IFormCheckboxDescription
          v-if="
            field.helpText &&
            (field.helpTextDisplay === 'text' ||
              field.helpTextDisplay === 'icon')
          "
        >
          {{ field.helpText }}
        </IFormCheckboxDescription>
      </IFormCheckboxField>
    </FormFieldGroup>

    <IAlert
      v-if="field.helpText && field.helpTextDisplay === 'alert'"
      class="mt-3"
    >
      <IAlertBody>
        {{ field.helpText }}
      </IAlertBody>
    </IAlert>
  </BaseFormField>
</template>

<script setup>
import isNil from 'lodash/isNil'

import FormFieldGroup from '../FormFieldGroup.vue'

const props = defineProps({
  field: { type: Object, required: true },
  resourceName: String,
  resourceId: [String, Number],
  validationErrors: Object,
  isFloating: Boolean,
})

const emit = defineEmits(['setInitialValue'])

const model = defineModel()

function setInitialValue() {
  emit(
    'setInitialValue',
    !isNil(props.field.value) ? props.field.value : props.field.falseValue
  )
}

setInitialValue()
</script>
