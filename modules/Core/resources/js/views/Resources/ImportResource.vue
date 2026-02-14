<template>
  <MainLayout>
    <ResourceImport :resource-name="resourceName" />
  </MainLayout>
</template>

<script setup>
import ResourceImport from '@/Core/components/Resource/ResourceImport.vue'

defineProps({
  resourceName: { type: String, required: true },
})
</script>
