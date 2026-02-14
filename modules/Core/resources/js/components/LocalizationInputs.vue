<template>
  <div>
    <IFormGroup
      v-if="withTimezoneField"
      label-for="timezone"
      :label="$t('core::app.timezone')"
    >
      <InputTimezone
        :model-value="form.timezone"
        @update:model-value="$emit('update:timezone', $event)"
      />

      <IFormError :error="form.getError('timezone')" />
    </IFormGroup>

    <IFormGroup
      v-if="withLocaleField"
      label-for="locale"
      :label="$t('core::app.locale')"
    >
      <ICustomSelect
        input-id="locale"
        :model-value="selectedLocale"
        :clearable="false"
        :options="locales"
        @update:model-value="$emit('update:locale', $event.value)"
      />

      <IFormError :error="form.getError('locale')" />
    </IFormGroup>

    <IFormGroup
      label-for="date_format"
      :label="$t('core::settings.date_format')"
    >
      <InputDateFormat
        :model-value="form.date_format"
        @update:model-value="$emit('update:dateFormat', $event)"
      />

      <IFormError :error="form.getError('date_format')" />
    </IFormGroup>

    <IFormGroup
      label-for="time_format"
      :label="$t('core::settings.time_format')"
    >
      <InputTimeFormat
        :model-value="form.time_format"
        @update:model-value="$emit('update:timeFormat', $event)"
      />

      <IFormError :error="form.getError('time_format')" />
    </IFormGroup>
  </div>
</template>

<script setup>
import { computed } from 'vue'

import InputDateFormat from '@/Core/components/InputDateFormat.vue'
import InputTimeFormat from '@/Core/components/InputTimeFormat.vue'
import InputTimezone from '@/Core/components/InputTimezone.vue'
import { useApp } from '@/Core/composables/useApp'

const props = defineProps({
  timeFormat: {},
  dateFormat: {},
  locale: {},
  timezone: {},
  form: { required: true, type: Object },
  exclude: { type: Array, default: () => [] },
})

defineEmits([
  'update:timeFormat',
  'update:dateFormat',
  'update:locale',
  'update:timezone',
])

const { locales } = useApp()

const withTimezoneField = computed(
  () => props.exclude.indexOf('timezone') === -1
)

const selectedLocale = computed(() =>
  locales.value.find(l => l.value === props.form.locale)
)

const withLocaleField = computed(() => props.exclude.indexOf('locale') === -1)
</script>
