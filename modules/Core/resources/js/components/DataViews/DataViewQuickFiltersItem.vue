<template>
  <div class="inline-flex items-center focus-within:z-10">
    <IDropdown placement="bottom-start">
      <IDropdownButton
        v-i-tooltip="rule.quickFilter.label ? rule.label : null"
        :active="isApplied"
        :title="dropdownButtonTitle"
        :class="isApplied && 'relative rounded-r-none focus:z-10'"
        :text="rule.quickFilter.label || rule.label"
        basic
      />

      <IDropdownMenu>
        <div
          v-if="
            rule.quickFilter.options.length >
            SHOW_SEARCH_INPUT_WHEN_OPTIONS_MORE_THAN
          "
          class="mb-1 rounded-md bg-neutral-100 p-3 dark:bg-neutral-900/50"
        >
          <InputSearch v-model="search" />
        </div>

        <div class="max-h-72 overflow-y-auto py-1">
          <template v-for="option in filteredRuleOptions" :key="option.value">
            <IDropdownSeparator v-if="option.separator === true" />

            <IDropdownItem
              v-else
              :class="[
                'min-w-36 cursor-pointer',
                option.bold ? 'font-semibold' : '',
              ]"
              :active="isOptionSelected(option)"
              @click="toggleFilterOption(option, $event)"
            >
              <template v-if="!multiple">
                <template v-if="!option.swatch_color">
                  {{ option.label }}
                </template>

                <IBadge v-else :color="option.swatch_color">
                  {{ option.label }}
                </IBadge>
              </template>

              <IFormCheckboxField v-else>
                <IFormCheckbox :checked="isOptionSelected(option)" />

                <IFormCheckboxLabel>
                  <template v-if="!option.swatch_color">
                    {{ option.label }}
                  </template>

                  <IBadge v-else :color="option.swatch_color">
                    {{ option.label }}
                  </IBadge>
                </IFormCheckboxLabel>
              </IFormCheckboxField>
            </IDropdownItem>
          </template>
        </div>
      </IDropdownMenu>
    </IDropdown>

    <IButton
      v-show="isApplied"
      icon="XSolid"
      class="rounded-l-none"
      :active="isApplied"
      basic
      @click="clearValue"
    />
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'

import { isBlank } from '@/Core/utils'

const props = defineProps({
  identifier: { type: String, required: true },
  rule: { type: Object, required: true },
  modelValue: {},
})

const emit = defineEmits(['changed', 'update:modelValue'])

const SHOW_SEARCH_INPUT_WHEN_OPTIONS_MORE_THAN = 5
const search = ref(null)
const multiple = computed(() => props.rule.quickFilter.multiple)

const dropdownButtonTitle = computed(() => {
  if (!isApplied.value) return null

  if (multiple.value !== true) {
    return props.rule.quickFilter.options.find(
      option => option.value == props.modelValue
    )?.label
  }

  return props.rule.quickFilter.options
    .filter(option => props.modelValue.includes(option.value))
    .map(option => option.label)
    .join(', ')
})

const filteredRuleOptions = computed(() => {
  const { options } = props.rule.quickFilter

  return !search.value
    ? options
    : options.filter(option =>
        option.separator === true
          ? true
          : option.label.toLowerCase().includes(search.value.toLowerCase())
      )
})

const isApplied = computed(() => !isBlank(props.modelValue))

function isOptionSelected(option) {
  return multiple.value === false
    ? props.modelValue == option.value
    : props.modelValue.includes(option.value)
}

function toggleFilterOption(option, e) {
  const { value } = option

  if (multiple.value === false) {
    emitEvents(props.modelValue == value ? null : value, { option })

    return
  }

  // https://github.com/vuejs/vue/issues/5650#issuecomment-300701271
  e.preventDefault()

  let newValue = null

  setTimeout(() => {
    if (props.modelValue.includes(value)) {
      newValue = props.modelValue.filter(optionId => optionId != value)
    } else {
      newValue = [...props.modelValue]
      newValue.push(value)
    }
    emitEvents(newValue, { option })
  })
}

function clearValue() {
  emitEvents(multiple.value ? [] : null)
}

function emitEvents(newValue, payload = {}) {
  emit('update:modelValue', newValue)

  emit('changed', {
    rule: props.rule,
    value: newValue,
    ...payload,
  })
}
</script>
