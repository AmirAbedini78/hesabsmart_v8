<template>
  <IFormSelect>
    <option
      v-for="format in $scriptConfig('time_formats')"
      :key="format"
      :value="format"
    >
      {{ format }} [{{ localizedTime(new Date(), format) }}]
    </option>
  </IFormSelect>
</template>

<script setup>
import { useDates } from '../composables/useDates'

const { localizedTime } = useDates()
</script>
