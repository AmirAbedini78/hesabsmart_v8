<template>
  <MainLayout>
    <ResourceTable :resource-name="resourceName" />
  </MainLayout>
</template>

<script setup>
const resourceName = Innoclapps.resourceName('calls')
</script>
