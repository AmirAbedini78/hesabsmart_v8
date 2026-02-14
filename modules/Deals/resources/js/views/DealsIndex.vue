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
              name: 'import-deal',
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

        <DealsNavbarViewSelector active="index" />

        <IButton
          v-show="!tableEmpty"
          variant="primary"
          icon="PlusSolid"
          :disabled="!resourceInformation.authorizedToCreate"
          :to="{ name: 'create-deal' }"
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
          to: { name: 'create-deal' },
          title: $t('deals::deal.empty_state.title'),
          buttonText: $t('deals::deal.create'),
          description: $t('deals::deal.empty_state.description'),
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
        :data-request-query-string="{ pipeline_id: activePipelineId }"
        @loaded="handleTableLoaded"
      >
        <template #header="{ meta }">
          <div class="w-52">
            <DealsPipelinesDropdown
              @activated="activePipelineId = $event"
              @changed="activePipelineId = $event"
            />
          </div>

          <ITextBlockDark class="inline-flex shrink-0 items-center font-medium">
            <span v-text="formatMoney(meta.summary?.value || 0)" />

            <span
              v-show="
                !isFilteringWonOrLostDeals &&
                meta.summary?.weighted_value > 0 &&
                meta.summary?.weighted_value !== meta.summary?.value
              "
              class="ml-2"
              v-text="'-'"
            />

            <span
              v-show="
                !isFilteringWonOrLostDeals &&
                meta.summary?.weighted_value > 0 &&
                meta.summary?.weighted_value !== meta.summary?.value
              "
              class="inline-flex items-center"
            >
              <Icon icon="Scale" class="mr-1 size-4" />

              <span>
                {{ formatMoney(meta.summary?.weighted_value) }}
              </span>
            </span>

            <span class="ml-2" v-text="'-'" />
          </ITextBlockDark>
        </template>

        <template #status="{ row, column }">
          <IBadge
            :variant="column.statuses[row.status]?.badge"
            :text="
              $t('deals::deal.status.' + column.statuses[row.status]?.name)
            "
          />
        </template>
      </ResourceTable>
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
import { useAccounting } from '@/Core/composables/useAccounting'
import { emitGlobal } from '@/Core/composables/useGlobalEventListener'
import { useQueryBuilder } from '@/Core/composables/useQueryBuilder'
import { useTable } from '@/Core/composables/useTable'

import DealsNavbarViewSelector from '../components/DealsNavbarViewSelector.vue'
import DealsPipelinesDropdown from '../components/DealsPipelinesDropdown.vue'

const resourceName = Innoclapps.resourceName('deals')
const resourceInformation = Innoclapps.resource(resourceName)

const { reloadTable } = useTable(resourceName)

const tableEmpty = ref(true)
const tableLoaded = ref(false)
const activePipelineId = ref(null)

const { formatMoney } = useAccounting()
const { findRule } = useQueryBuilder(resourceName)
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

const isFilteringWonOrLostDeals = computed(() => {
  let rule = findRule('status')

  if (!rule) return false

  return rule.query.value === 'won' || rule.query.value === 'lost'
})
</script>
