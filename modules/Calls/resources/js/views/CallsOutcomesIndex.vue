<template>
  <ResourceSimpleManager
    :resource-name="resourceName"
    :with-cancel="false"
    @created="fetchCallOutcomes"
    @updated="fetchCallOutcomes"
    @deleted="fetchCallOutcomes"
  >
    <template #header>
      <ICardHeading :text="$t('calls::call.outcome.outcomes')" />
    </template>
  </ResourceSimpleManager>
</template>

<script setup>
import ResourceSimpleManager from '@/Core/components/Resource/ResourceSimpleManager.vue'

import { useCallOutcomes } from '../composables/useCallOutcomes'

const resourceName = Innoclapps.resourceName('call-outcomes')

const { fetchCallOutcomes } = useCallOutcomes()
</script>
