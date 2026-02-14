<template>
  <ResourceSimpleManager
    :resource-name="resourceName"
    :with-cancel="false"
    @created="fetchLostReasons"
    @updated="fetchLostReasons"
    @deleted="fetchLostReasons"
  >
    <template #header>
      <ICardHeading :text="$t('deals::deal.lost_reasons.lost_reasons')" />
    </template>
  </ResourceSimpleManager>
</template>

<script setup>
import ResourceSimpleManager from '@/Core/components/Resource/ResourceSimpleManager.vue'

import { useLostReasons } from '../composables/useLostReasons'

const resourceName = Innoclapps.resourceName('lost-reasons')

const { fetchLostReasons } = useLostReasons()
</script>
