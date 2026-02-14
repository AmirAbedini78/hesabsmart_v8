<!-- eslint-disable vue/no-v-html -->
<template>
  <EditorText class="mb-3">
    <div v-html="section.message" />
  </EditorText>
</template>

<script setup>
import propsDefinition from './props'

defineProps(propsDefinition)
</script>
