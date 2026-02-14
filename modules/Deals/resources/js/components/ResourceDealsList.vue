<template>
  <DealsList
    :deals="resource.deals"
    :empty-text="
      $t('core::resource.has_no_associations', {
        related_resource: resourceInformation.singularLabel,
        resource: $t('deals::deal.deals'),
      })
    "
  >
    <template #actions="{ deal }">
      <IButton
        v-if="showDissociateButton"
        v-i-tooltip.left="$t('deals::deal.dissociate')"
        icon="XSolid"
        basic
        small
        @click="$confirm(() => dissociateDeal(deal.id))"
      />
    </template>

    <template #top-actions>
      <IButton
        v-if="showCreateButton"
        v-i-tooltip="$t('deals::deal.add')"
        class="-my-1.5 ml-4"
        icon="PlusSolid"
        basic
        small
        @click="$emit('createRequested')"
      />
    </template>
  </DealsList>
</template>

<script setup>
import { inject } from 'vue'
import { useI18n } from 'vue-i18n'

import DealsList from './DealsList.vue'

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

async function dissociateDeal(id) {
  await detachResourceAssociations({ deals: [id] })

  emit('dissociated', id)

  Innoclapps.success(t('core::resource.dissociated'))
}
</script>
