<template>
  <IDropdown placement="bottom-end">
    <IDropdownButton :text="$t('core::actions.actions')" basic />

    <IDropdownMenu>
      <IDropdownItem
        v-for="action in actions"
        :key="action.uriKey"
        :text="action.name"
        @click="$emit('change', action)"
      />
    </IDropdownMenu>
  </IDropdown>
</template>

<script setup>
defineProps({
  actions: { type: Array, default: () => [] },
})

defineEmits(['change'])
</script>
