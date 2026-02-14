<template>
  <IText>
    <I18nT
      scope="global"
      class="flex w-full flex-wrap items-start font-medium sm:items-center"
      tag="p"
      :keypath="keypath"
    >
      <template #condition>
        {{
          $t(
            'core::filters.conditions.' +
              ((displayableCondition || condition) === 'all' ? 'and' : 'or')
          )
        }}
      </template>

      <template #match_type>
        <select
          v-model="condition"
          :class="[
            'border-1 mx-1 rounded-md border-neutral-300 bg-none px-2 py-0 text-base/6 focus:shadow-none focus:ring-primary-500 dark:border-neutral-500 dark:bg-neutral-500 dark:text-white sm:text-sm/6',
            disabled ? 'pointer-events-none' : '',
          ]"
        >
          <option value="all">{{ labels.matchTypeAll }}</option>

          <option value="any">{{ labels.matchTypeAny }}</option>
        </select>
      </template>
    </I18nT>
  </IText>
</template>

<script setup>
import { onMounted } from 'vue'

defineProps({
  labels: { type: Object, required: true },
  keypath: { type: String, required: true },
  disabled: Boolean,
  displayableCondition: String,
})

const condition = defineModel({ type: String, required: true })

// Backward compatibility.
onMounted(() => {
  if (condition.value === 'and') {
    condition.value = 'all'
  } else if (condition.value === 'or') {
    condition.value = 'any'
  }
})
</script>
