<template>
  <Panel :panel="panel">
    <ResourceContactsList
      :resource="resource"
      :resource-name="resourceName"
      :resource-id="resourceId"
      :show-create-button="resource.authorizations.update"
      :show-dissociate-button="resource.authorizations.update"
      @create-requested="showCreateModal = true"
    />

    <ContactsCreate
      v-if="showCreateModal"
      :via-resource="resourceName"
      :parent-resource="resource"
      :go-to-list="false"
      @associated="fetchResource(), (showCreateModal = false)"
      @created="
        ({ isRegularAction }) => (
          isRegularAction ? (showCreateModal = false) : '', fetchResource()
        )
      "
      @hidden="showCreateModal = false"
    />
  </Panel>
</template>

<script setup>
import { inject, ref } from 'vue'

import ContactsCreate from '../views/ContactsCreate.vue'

import ResourceContactsList from './ResourceContactsList.vue'

defineProps({
  resourceName: { required: true, type: String },
  resourceId: { required: true, type: [String, Number] },
  resource: { required: true, type: Object },
  panel: { required: true, type: Object },
})

const fetchResource = inject('fetchResource')

const showCreateModal = ref(false)
</script>
