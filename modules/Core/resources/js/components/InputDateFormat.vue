<template>
  <IFormSelect>
    <option
      v-for="format in $scriptConfig('date_formats')"
      :key="format"
      :value="format"
    >
      {{ format }} [{{ localizedDate(new Date(), format) }}]
    </option>
  </IFormSelect>
</template>

<script setup>
import { useDates } from '../composables/useDates'

const { localizedDate } = useDates()
</script>
