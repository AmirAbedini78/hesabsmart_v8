<template>
  <MainLayout>
    <ResourceTrashedTable :resource-name="resourceName" />
  </MainLayout>
</template>

<script setup>
import ResourceTrashedTable from '../../components/Resource/Table/ResourceTrashedTable.vue'

defineProps({
  resourceName: { type: String, required: true },
})
</script>
