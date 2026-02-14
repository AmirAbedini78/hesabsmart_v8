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
            icon="Bars3CenterLeft"
            :to="{
              name: 'document-templates-index',
            }"
            :text="$t('documents::document.template.manage')"
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
          :to="{ name: 'create-document' }"
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
          to: { name: 'create-document' },
          title: $t('documents::document.empty_state.title'),
          buttonText: $t('documents::document.create'),
          description: $t('documents::document.empty_state.description'),
        }"
      />
    </div>

    <CardsRenderer
      v-if="shouldShowCards"
      class="mb-6"
      :resource-name="resourceName"
    />

    <div v-show="!tableEmpty">
      <ResourceTable :resource-name="resourceName" @loaded="handleTableLoaded">
        <template #status="{ row }">
          <IBadge
            :color="statuses[row.status].color"
            :text="statuses[row.status].display_name"
          />
        </template>
      </ResourceTable>
    </div>

    <!-- Create router view -->
    <RouterView name="create" @created="refreshIndex" @sent="refreshIndex" />

    <!-- Edit router view -->
    <RouterView
      name="edit"
      :exit-using="() => $router.push({ name: 'document-index' })"
      @changed="refreshIndex"
      @deleted="refreshIndex"
      @cloned="refreshIndex"
    />
  </MainLayout>
</template>

<script setup>
import { computed, ref } from 'vue'

import CardsRenderer from '@/Core/components/Cards/CardsRenderer.vue'
import { useApp } from '@/Core/composables/useApp'
import { emitGlobal } from '@/Core/composables/useGlobalEventListener'
import { useTable } from '@/Core/composables/useTable'

const resourceName = Innoclapps.resourceName('documents')
const resourceInformation = Innoclapps.resource(resourceName)

const { reloadTable } = useTable(resourceName)
const { scriptConfig } = useApp()
const statuses = scriptConfig('documents.statuses')

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
