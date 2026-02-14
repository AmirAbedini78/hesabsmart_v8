<template>
  <ContactsList
    :contacts="resource.contacts"
    :empty-text="
      $t('core::resource.has_no_associations', {
        related_resource: resourceInformation.singularLabel,
        resource: $t('contacts::contact.contacts'),
      })
    "
  >
    <template #actions="{ contact }">
      <IButton
        v-if="showDissociateButton"
        v-i-tooltip.left="$t('contacts::contact.dissociate')"
        icon="XSolid"
        basic
        small
        @click="$confirm(() => dissocateContact(contact.id))"
      />
    </template>

    <template #top-actions>
      <IButton
        v-if="showCreateButton"
        v-i-tooltip="$t('contacts::contact.add')"
        class="-my-1.5 ml-4"
        icon="PlusSolid"
        basic
        small
        @click="$emit('createRequested')"
      />
    </template>
  </ContactsList>
</template>

<script setup>
import { inject } from 'vue'
import { useI18n } from 'vue-i18n'

import ContactsList from './ContactsList.vue'

const props = defineProps({
  resourceName: { required: true, type: String },
  resourceId: { required: true, type: [String, Number] },
  resource: { required: true, type: Object },
  showCreateButton: { type: Boolean, required: true },
  showDissociateButton: { type: Boolean, required: true },
})

const emit = defineEmits(['dissociated', 'createRequested'])

const resourceInformation = Innoclapps.resource(props.resourceName)

const { t } = useI18n()

const detachResourceAssociations = inject('detachResourceAssociations')

async function dissocateContact(id) {
  await detachResourceAssociations({ contacts: [id] })

  emit('dissociated', id)

  Innoclapps.success(t('core::resource.dissociated'))
}
</script>
