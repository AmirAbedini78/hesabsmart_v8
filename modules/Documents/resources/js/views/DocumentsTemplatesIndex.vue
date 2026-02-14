<template>
  <MainLayout>
    <template #actions>
      <NavbarSeparator class="hidden lg:block" />

      <IButton
        v-show="!tableEmpty"
        variant="primary"
        icon="PlusSolid"
        :to="{ name: 'create-document-template' }"
        :text="
          $t('core::resource.create', {
            resource: resourceInformation.singularLabel,
          })
        "
      />
    </template>

    <IOverlay v-if="!tableLoaded" show />

    <div v-if="shouldShowEmptyState" class="m-auto mt-8 max-w-5xl">
      <IEmptyState
        v-bind="{
          to: { name: 'create-document-template' },
          title: $t('documents::document.template.empty_state.title'),
          buttonText: $t('documents::document.template.create'),
          description: $t(
            'documents::document.template.empty_state.description'
          ),
        }"
      />
    </div>

    <div v-show="!tableEmpty">
      <ResourceTable
        :resource-name="resourceName"
        @loaded="handleTableLoaded"
      />
    </div>

    <!-- Create, Edit -->
    <RouterView name="create" @created="reloadTable" />

    <RouterView name="edit" @updated="reloadTable" />
  </MainLayout>
</template>

<script setup>
import { computed, ref } from 'vue'

import { useTable } from '@/Core/composables/useTable'

const resourceName = Innoclapps.resourceName('document-templates')
const resourceInformation = Innoclapps.resource(resourceName)

const { reloadTable } = useTable(resourceName)

const tableEmpty = ref(true)
const tableLoaded = ref(false)

const shouldShowEmptyState = computed(
  () => tableEmpty.value && tableLoaded.value
)

function handleTableLoaded(e) {
  tableEmpty.value = e.isPreEmpty
  tableLoaded.value = true
}
</script>
