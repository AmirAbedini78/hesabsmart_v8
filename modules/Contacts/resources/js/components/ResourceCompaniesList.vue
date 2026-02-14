<template>
  <CompaniesList
    :companies="resource.companies"
    :empty-text="
      $t('core::resource.has_no_associations', {
        related_resource: resourceInformation.singularLabel,
        resource: $t('contacts::company.companies'),
      })
    "
  >
    <template #actions="{ company }">
      <IButton
        v-if="showDissociateButton"
        v-i-tooltip.left="$t('contacts::company.dissociate')"
        icon="XSolid"
        basic
        small
        @click="$confirm(() => dissociateCompany(company.id))"
      />
    </template>

    <template #top-actions>
      <IButton
        v-if="showCreateButton"
        v-i-tooltip="$t('contacts::company.add')"
        class="-my-1.5 ml-4"
        icon="PlusSolid"
        basic
        small
        @click="$emit('createRequested')"
      />
    </template>
  </CompaniesList>
</template>

<script setup>
import { inject } from 'vue'
import { useI18n } from 'vue-i18n'

import CompaniesList from './CompaniesList.vue'

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

async function dissociateCompany(id) {
  await detachResourceAssociations({ companies: [id] })

  emit('dissociated', id)

  Innoclapps.success(t('core::resource.dissociated'))
}
</script>
