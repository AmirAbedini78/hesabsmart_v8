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

        <ActivitiesNavbarViewSelector active="index" />

        <IButton
          v-show="!tableEmpty"
          variant="primary"
          icon="PlusSolid"
          :disabled="!resourceInformation.authorizedToCreate"
          :to="{ name: 'create-activity' }"
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
          to: { name: 'create-activity' },
          title: $t('activities::activity.empty_state.title'),
          buttonText: $t('activities::activity.create'),
          description: $t('activities::activity.empty_state.description'),
          secondButtonText: $t('core::import.from_file', { file_type: 'CSV' }),
          secondButtonIcon: 'DocumentAdd',
          secondButtonTo: {
            name: 'import-resource',
            params: { resourceName },
          },
        }"
      />
    </div>

    <div v-show="!tableEmpty">
      <ResourceTable :resource-name="resourceName" @loaded="handleTableLoaded">
        <template #title="{ row }">
          <div class="flex items-center space-x-1">
            <ActivityStateChange
              v-if="row.authorizations.update"
              class="mr-1 mt-0.5"
              :is-completed="row.is_completed"
              :activity-id="row.id"
              @changed="reloadTable"
            />

            <span>
              {{ row.title }}
            </span>
          </div>
        </template>
      </ResourceTable>
    </div>

    <ResourceExport :resource-name="resourceName" />

    <!-- Create -->
    <RouterView
      name="create"
      @created="
        ({ isRegularAction, action }) => (
          reloadTable(),
          isRegularAction || action === 'go-to-list'
            ? $router.back()
            : undefined
        )
      "
      @hidden="$router.back"
    />

    <!-- Edit/View -->
    <RouterView name="edit" />
  </MainLayout>
</template>

<script setup>
import { computed, ref } from 'vue'

import { useResourceable } from '@/Core/composables/useResourceable'
import { useTable } from '@/Core/composables/useTable'

import ActivitiesNavbarViewSelector from '../components/ActivitiesNavbarViewSelector.vue'
import ActivityStateChange from '../components/ActivityStateChange.vue'

const { resourceName, resourceInformation } = useResourceable('activities')
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
