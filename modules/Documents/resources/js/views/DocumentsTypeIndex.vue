<template>
  <ResourceSimpleManager
    :resource-name="resourceName"
    :with-cancel="false"
    @created="fetchDocumentTypes"
    @updated="fetchDocumentTypes"
    @deleted="fetchDocumentTypes"
  >
    <template #header>
      <ICardHeading :text="$t('documents::document.type.types')" />
    </template>
  </ResourceSimpleManager>
</template>

<script setup>
import ResourceSimpleManager from '@/Core/components/Resource/ResourceSimpleManager.vue'

import { useDocumentTypes } from '../composables/useDocumentTypes'

const resourceName = Innoclapps.resourceName('document-types')

const { fetchDocumentTypes } = useDocumentTypes()
</script>
