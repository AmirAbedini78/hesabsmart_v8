<template>
  <ResourceSimpleManager
    :resource-name="resourceName"
    :with-cancel="false"
    @created="fetchActivityTypes"
    @updated="fetchActivityTypes"
    @deleted="fetchActivityTypes"
  >
    <template #header>
      <ICardHeading :text="$t('activities::activity.type.types')" />
    </template>
  </ResourceSimpleManager>
</template>

<script setup>
import ResourceSimpleManager from '@/Core/components/Resource/ResourceSimpleManager.vue'

import { useActivityTypes } from '../composables/useActivityTypes'

const resourceName = Innoclapps.resourceName('activity-types')

const { fetchActivityTypes } = useActivityTypes()
</script>
