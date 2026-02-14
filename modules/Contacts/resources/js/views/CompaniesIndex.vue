<template>
  <MainLayout>
    <template #actions>
      <NavbarSeparator class="hidden lg:block" />

      <NavbarItems>
        <IDropdownMinimal
          :placement="tableEmpty ? 'bottom-end' : 'bottom'"
          horizontal
        >
          <IDropdownItem
            v-if="resourceInformation.authorizedToImport"
            icon="DocumentAdd"
            :to="{
              name: 'import-resource',
              params: { resourceName },
            }"
            :text="
              $t('core::resource.import', {
                resource: resourceInformation.label,
              })
            "
          />

          <IDropdownItem
            v-if="resourceInformation.authorizedToExport"
            icon="DocumentDownload"
            :text="
              $t('core::resource.export', {
                resource: resourceInformation.label,
              })
            "
            @click="$dialog.show('export-modal')"
          />

          <IDropdownItem
            v-if="resourceInformation.usesSoftDeletes"
            icon="Trash"
            :to="{
              name: 'trashed-resource-records',
              params: { resourceName },
            }"
            :text="
              $t('core::resource.trashed', {
                resource: resourceInformation.label,
              })
            "
          />
        </IDropdownMinimal>

        <IButton
          v-show="!tableEmpty"
          variant="primary"
          icon="PlusSolid"
          :disabled="!resourceInformation.authorizedToCreate"
          :to="{ name: 'create-company' }"
          :text="
            $t('core::resource.create', {
              resource: resourceInformation.singularLabel,
            })
          "
        />
      </NavbarItems>
    </template>

    <IOverlay v-if="!tableLoaded" show />

    <div v-if="shouldShowEmptyState" class="m-auto mt-8 max-w-5xl">
      <IEmptyState
        v-bind="{
          to: { name: 'create-company' },
          title: $t('contacts::company.empty_state.title'),
          buttonText: $t('contacts::company.create'),
          description: $t('contacts::company.empty_state.description'),
          secondButtonText: $t('core::import.from_file', { file_type: 'CSV' }),
          secondButtonIcon: 'DocumentAdd',
          secondButtonTo: {
            name: 'import-resource',
            params: { resourceName },
          },
        }"
      />
    </div>

    <CardsRenderer
      v-if="shouldShowCards"
      class="mb-6"
      :resource-name="resourceName"
    />

    <div v-show="!tableEmpty">
      <ResourceTable
        :resource-name="resourceName"
        @loaded="handleTableLoaded"
      />
    </div>

    <ResourceExport :resource-name="resourceName" />

    <!-- Create -->
    <RouterView
      name="create"
      :redirect-to-view="true"
      @created="
        ({ isRegularAction }) => (!isRegularAction ? refreshIndex() : undefined)
      "
      @hidden="$router.back"
    />
  </MainLayout>
</template>

<script setup>
import { computed, ref } from 'vue'

import CardsRenderer from '@/Core/components/Cards/CardsRenderer.vue'
import { emitGlobal } from '@/Core/composables/useGlobalEventListener'
import { useTable } from '@/Core/composables/useTable'

const resourceName = Innoclapps.resourceName('companies')
const resourceInformation = Innoclapps.resource(resourceName)

const { reloadTable } = useTable(resourceName)

const tableEmpty = ref(true)
const tableLoaded = ref(false)

const shouldShowCards = computed(() => !tableEmpty.value)

const shouldShowEmptyState = computed(
  () => tableEmpty.value && tableLoaded.value
)

function handleTableLoaded(e) {
  tableEmpty.value = e.isPreEmpty
  tableLoaded.value = true
}

function refreshIndex() {
  emitGlobal('refresh-cards')
  reloadTable()
}
</script>
