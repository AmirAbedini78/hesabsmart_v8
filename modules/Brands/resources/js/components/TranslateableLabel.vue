<template>
  <div class="mb-1 flex justify-between">
    <IFormLabel :for="labelFor" :label="label" />

    <IDropdown>
      <IDropdownButton :text="locale" basic small />

      <IDropdownMenu>
        <IDropdownItem
          v-for="availableLocale in locales"
          :key="availableLocale.value"
          :text="availableLocale.label"
          :active="availableLocale.value === locale"
          condensed
          @click="$emit('update:locale', availableLocale.value)"
        />
      </IDropdownMenu>
    </IDropdown>
  </div>
</template>

<script setup>
import { useApp } from '@/Core/composables/useApp'

defineProps(['labelFor', 'label', 'locale'])

defineEmits(['update:locale'])

const { locales } = useApp()
</script>
