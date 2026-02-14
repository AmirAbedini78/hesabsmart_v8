<template>
  <div class="grid grid-cols-12 gap-y-1">
    <div
      v-for="field in fields"
      :key="field.attribute"
      :class="[
        field.width === 'half' ? 'col-span-12 sm:col-span-12' : 'col-span-12',
        field.displayNone || (collapsed && field.collapsed)
          ? 'pointer-events-none hidden'
          : '',
      ]"
    >
      <component
        :is="field.detailComponent"
        :field="field"
        :resource-name="resourceName"
        :resource-id="resourceId"
        :resource="resource"
        :is-floating="isFloating"
        @updated="$emit('updated', $event)"
      />
    </div>
  </div>
</template>

<script setup>
defineProps({
  fields: { required: true, type: Array },
  collapsed: Boolean,
  isFloating: Boolean,
  resourceName: { required: true, type: String },
  resourceId: { required: true, type: Number },
  resource: { required: true, type: Object },
})

defineEmits(['updated'])
</script>
