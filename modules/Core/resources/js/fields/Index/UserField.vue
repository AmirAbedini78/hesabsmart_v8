<template>
  <BaseIndexField
    v-slot="{ hasValue, value }"
    :resource-name="resourceName"
    :resource-id="resourceId"
    :row="row"
    :field="field"
  >
    <span v-if="hasValue" class="flex items-center gap-x-1">
      <IAvatar size="xs" :src="value.avatar_url" :alt="value.name" />

      <span>
        {{ value.name }}
      </span>
    </span>

    <span v-else>&mdash;</span>
  </BaseIndexField>
</template>

<script setup>
defineProps(['column', 'row', 'field', 'resourceName', 'resourceId'])
</script>
