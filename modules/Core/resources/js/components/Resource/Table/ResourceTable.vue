<template>
  <IOverlay v-if="!initialDataLoaded" show />

  <div
    :class="
      shouldDisplayTableData ? 'opacity-100' : 'pointer-events-none opacity-0'
    "
  >
    <div
      v-if="withViews"
      :class="['mb-0 sm:mb-4', settings.singleViewOnly ? 'hidden' : '']"
    >
      <DataViews
        class="[--gutter:theme('spacing.8')]"
        :identifier="tableIdentifier"
        :single-view-only="settings.singleViewOnly"
        @changed="handleViewChanged"
      />
    </div>

    <div
      class="mb-4 space-y-2 sm:mt-0 md:flex md:items-center md:space-x-3.5 md:space-y-0"
    >
      <InputSearch
        v-model="search"
        class="min-w-48 sm:max-w-60"
        @update:model-value="request(true)"
      />

      <DataViewFilters
        v-if="hasRules"
        :identifier="tableIdentifier"
        @apply="applyFilters"
      />

      <div class="flex shrink-0 items-center gap-x-2">
        <slot
          name="header"
          :total="collection.state.meta.total"
          :meta="collection.state.meta"
          :is-pre-empty="isPreEmpty"
        />

        <ITextDark class="shrink-0 truncate font-semibold">
          {{ collection.state.meta.total }}
          <span class="lowercase" v-text="resource.label" />
        </ITextDark>
      </div>

      <div v-if="withViews" class="flex-1 text-right">
        <IButton
          v-if="settings.singleViewOnly"
          icon="Cog"
          basic
          @click="$refs.customizationRef.setVisibility(true)"
        />

        <ResourceTableCustomize
          ref="customizationRef"
          :identifier="tableIdentifier"
          :cache-key="cacheKey"
        />
      </div>
    </div>

    <IOverlay :show="isLoading">
      <ITableOuter class="relative mt-3">
        <ResourceTableActions
          v-if="isSelectable"
          :actions="settings.actions"
          :ids="selectedRowsIds"
          :run-action-request-additional-params="actionRequestAdditionalParams"
          :resource-name="resourceName"
          @action-executed="$emit('actionExecuted', $event)"
        />

        <ITable
          :id="'table-' + tableIdentifier"
          :class="[
            '[&_tbody>tr>td:first-child]:px-4 [&_thead>tr>th:first-child]:px-4',
            isEmpty
              ? '[&_.resizer]:pointer-events-none [&_.resizer]:!opacity-0'
              : '',
          ]"
          :condensed="isCondensed"
          :max-height="maxHeightPx"
          :grid="isBordered"
          fixed-layout
        >
          <ResourceTableHead
            :is-loaded="initialDataLoaded"
            :columns="columnsForResizer"
            :resize-disabled="!withViews || !authorizedToUpdateActiveView"
            @resized="handleColumnsResized"
          >
            <ResourceTableHeadRow
              v-model:columns="visibleColumns"
              :reorderable="settings.reorderable || false"
              :is-sticky="isSticky"
              :is-selectable="isSelectable"
              :is-condensed="isCondensed"
              :selected-rows-count="selectedRowsCount"
              :all-rows-selected="allRowsSelected"
              :with-views="withViews"
              :authorized-to-update-active-view="
                withViews && authorizedToUpdateActiveView
              "
              :move="onColumnMoveHandler"
              :is-ordered-by-callback="
                col => collection.isOrderedBy(col.attribute)
              "
              :is-sorted-ascending-callback="
                col => collection.isSorted('asc', col.attribute)
              "
              @update:columns="saveColumns($event, false)"
              @select-all="selectAllRows"
              @unselect-all="unselectAllRows"
              @sort-asc="sortAscending"
              @sort-desc="sortDescending"
              @customize-view-requested="
                $refs.customizationRef.setVisibility(true)
              "
            />
          </ResourceTableHead>

          <ITableBody>
            <ResourceTableRow
              v-for="row in collection.items"
              :key="row.id"
              :resource-name="resourceName"
              :row="row"
              :columns="visibleColumns"
              :is-selectable="isSelectable"
              :is-condensed="isCondensed"
              :actions="settings.actions"
              :selected-rows-count="selectedRowsCount"
              :run-action-request-additional-params="
                actionRequestAdditionalParams
              "
              @selected="selectRow(row)"
              @reload="request"
              @action-executed="$emit('actionExecuted', $event)"
            >
              <template v-for="(_, name) in $slots" #[name]="slotData">
                <slot :name="name" v-bind="slotData" />
              </template>
            </ResourceTableRow>

            <ITableRow v-if="isEmpty" data-slot="empty">
              <ITableCell v-show="initialDataLoaded" :colspan="totalColumns">
                <IText>{{ emptyText }}</IText>
              </ITableCell>
            </ITableRow>
          </ITableBody>
        </ITable>

        <TablePagination
          v-if="collection.hasPagination"
          class="px-4 py-3"
          :is-current-page-check="page => collection.isCurrentPage(page)"
          :has-next-page="collection.hasNextPage"
          :has-previous-page="collection.hasPreviousPage"
          :links="collection.pagination"
          :render-links="collection.shouldRenderLinks"
          :from="collection.from"
          :to="collection.to"
          :total="collection.total"
          :loading="isLoading"
          @go-to-next="collection.nextPage()"
          @go-to-previous="collection.previousPage()"
          @go-to-page="collection.page($event)"
        />
      </ITableOuter>
    </IOverlay>
  </div>
</template>

<script setup>
import {
  computed,
  nextTick,
  onBeforeUnmount,
  onMounted,
  reactive,
  ref,
  watch,
} from 'vue'
import { useI18n } from 'vue-i18n'
import { useTimeoutFn } from '@vueuse/core'
import isEqual from 'lodash/isEqual'
import omit from 'lodash/omit'

import { useApp } from '../../../composables/useApp'
import { useDataViews } from '../../../composables/useDataViews'
import { useGlobalEventListener } from '../../../composables/useGlobalEventListener'
import { useLoader } from '../../../composables/useLoader'
import { useQueryBuilder } from '../../../composables/useQueryBuilder'
import { useTable } from '../../../composables/useTable'
import { CancelToken } from '../../../services/HTTP'
import DataViewFilters from '../../DataViews/DataViewFilters.vue'
import DataViews from '../../DataViews/DataViews.vue'
import Collection from '../../Table/Collection'
import TablePagination from '../../Table/TablePagination.vue'

import ResourceTableActions from './ResourceTableActions.vue'
import ResourceTableCustomize from './ResourceTableCustomize.vue'
import ResourceTableHead from './ResourceTableHead.vue'
import ResourceTableHeadRow from './ResourceTableHeadRow.vue'
import ResourceTableRow from './ResourceTableRow.vue'

const props = defineProps({
  resourceName: { type: String, required: true },
  identifier: String,
  runActionRequestAdditionalParams: { type: Object, default: () => ({}) },
  dataRequestQueryString: { type: Object, default: () => ({}) },
  trashed: Boolean,
  urlPath: String,
  cacheKey: String,
})

const emit = defineEmits(['loaded', 'actionExecuted'])

let clearPollingIntervalId = null
let demoResizeMessageDisplayed = false
let demoHideMessageDisplayed = false
let watchersInitialized = false
let unwatch = []

const tableIdentifier = computed(() => props.identifier || props.resourceName)
const resource = Innoclapps.resource(props.resourceName)

const { t } = useI18n()
const { setLoading, isLoading } = useLoader()
const { scriptConfig } = useApp()

const {
  settings,
  fetchActions,
  fetchSettings,
  columns,
  perPage,
  isCondensed,
  isBordered,
  pollingInterval,
  defaultOrder,
  maxHeightPx,
  isSticky,
} = useTable(tableIdentifier.value, () => props.cacheKey)

const { availableRules, queryBuilderRules, rulesAreValid, hasRules } =
  useQueryBuilder(tableIdentifier)

const { views, activeView, patchView } = useDataViews(tableIdentifier)

const collection = reactive(new Collection())
const search = ref('')
const componentReady = ref(false)
const initialDataLoaded = ref(false)
const customizationRef = ref(null)

let requestCancelToken = null

const withViews = computed(
  () => !props.trashed && (settings.value?.withViews || false)
)

const authorizedToUpdateActiveView = computed(() =>
  activeView.value ? activeView.value.authorizations.update : false
)

const emptyText = computed(() => {
  if (collection.isNotEmpty()) return ''
  if (isLoading.value) return '...'
  if (search.value) return t('core::app.no_search_results')

  return t('core::table.empty')
})

const isPreEmpty = computed(() => collection.state.meta.pre_total === 0)

const isEmpty = computed(() => collection.isEmpty())

const actionRequestAdditionalParams = computed(() => ({
  ...props.runActionRequestAdditionalParams,
  trashed: props.trashed || undefined,
}))

const commonDataRequestQueryString = computed(() => ({
  trashed: props.trashed || undefined,
  ...props.dataRequestQueryString,
}))

const tableRequestQueryString = computed(() => ({
  page: collection.currentPage,
  per_page: collection.perPage,
  order: collection.get('order'),
  ...commonDataRequestQueryString.value,
  ...settings.value.requestQueryString,
}))

const visibleColumns = computed({
  get() {
    return columns.value.filter(column => column.hidden == false)
  },
  set(value) {
    // We will make sure to update the active view before the "save" request
    // so the changes are reflected on the ui immediately without
    // the user to wait the "save" request to finish.
    patchView({
      ...activeView.value,
      config: {
        ...activeView.value.config,
        table: {
          ...activeView.value.config.table,
          columns: activeView.value.config.table.columns.map(col => {
            const newValueColumn = value.find(
              vc => vc.attribute === col.attribute
            )

            return newValueColumn
              ? {
                  ...col,
                  ...newValueColumn,
                  order: value.indexOf(newValueColumn) + 1,
                  hidden: newValueColumn.visible === false,
                }
              : col
          }),
        },
      },
    })
  },
})

const columnsForResizer = computed(() =>
  visibleColumns.value.map(column => ({
    attribute: column.attribute,
    width: column.width,
    minWidth: column.minWidth,
    resizeable: column.resizeable,
  }))
)

const totalColumns = computed(() => visibleColumns.value.length)

const shouldDisplayTableData = computed(() => initialDataLoaded.value)

const computedUrlPath = computed(
  () => props.urlPath || '/' + props.resourceName + '/' + 'table'
)

const selectedRows = computed(() =>
  collection.items.filter(row => row.tSelected)
)

const selectedRowsCount = computed(() => selectedRows.value.length)

const selectedRowsIds = computed(() => selectedRows.value.map(row => row.id))

const allRowsSelected = computed(
  () => selectedRowsCount.value === collection.items.length
)

const isSelectable = computed(() => {
  return (
    collection.items.length > 0 &&
    settings.value &&
    settings.value.actions &&
    settings.value.actions.length > 0
  )
})

async function request(viaUserSearch = false) {
  if (isLoading.value) return

  cancelPreviousRequest()
  setLoading(true)

  // Reset the current page as the search won't be accurate as there will
  // be offset on the query and if any results are found, won't be queried
  if (viaUserSearch && collection.currentPage !== 1) {
    setPage(1)
  }

  const params = { ...tableRequestQueryString.value }

  if (rulesAreValid.value) params.filters = queryBuilderRules.value
  if (search.value) params.q = search.value
  if (activeView.value) params.view = activeView.value.id

  try {
    const response = await Innoclapps.request(computedUrlPath.value, {
      params,
      cancelToken: new CancelToken(token => (requestCancelToken = token)),
    })

    collection.setState(response.data)

    emit('loaded', { isPreEmpty: isPreEmpty.value })
  } catch (error) {
    console.error(error)
  } finally {
    handleRequestFinished()
  }
}

function sortAscending(column) {
  collection.sortAsc(column)
  handleSortChanged(column, 'asc')
}

function sortDescending(column) {
  collection.sortDesc(column)
  handleSortChanged(column, 'desc')
}

function handleSortChanged(attribute, direction) {
  settings.value = {
    ...settings.value,
    currentOrder: [{ attribute, direction }],
  }
}

function handleRequestFinished() {
  setLoading(false)

  if (!initialDataLoaded.value) {
    useTimeoutFn(() => {
      initialDataLoaded.value = true
    }, 150)
  }
}

function saveColumns(columns, refreshTable = true) {
  if (!scriptConfig('demo')) {
    customizationRef.value.save(columns, refreshTable !== false)
  } else if (!demoHideMessageDisplayed) {
    Innoclapps.info(
      'The state of the columns is not saved in the demo and will reset upon refreshing the page.',
      7000
    )
    demoHideMessageDisplayed = true
  }
}

function handleColumnsResized(columns) {
  if (scriptConfig('demo')) {
    displayDemoResizeMessage()

    return
  }

  updateColumnWidths(columns)
}

function displayDemoResizeMessage() {
  if (!demoResizeMessageDisplayed) {
    Innoclapps.info(
      'Column resizing is not saved in the demo and will reset upon navigating away from this page.',
      7000
    )
    demoResizeMessageDisplayed = true
  }
}

function updateColumnWidths(columns) {
  const updatedColumns = visibleColumns.value.map((column, index) =>
    column.resizeable
      ? {
          ...column,
          width: columns[index].width,
        }
      : column
  )

  visibleColumns.value = updatedColumns
  saveColumns(updatedColumns, false)
}

function configurePolling() {
  clearPollingIntervalId = setInterval(request, pollingInterval.value * 1000)
}

function clearPolling() {
  clearPollingIntervalId && clearInterval(clearPollingIntervalId)
}

function setDefaults() {
  collection.perPage = perPage.value
  collection.set('order', settings.value.currentOrder || defaultOrder.value)
  collection.currentPage = settings.value.currentPage || 1
}

function prepareComponent() {
  setDefaults()
  configureWatchers()
  componentReady.value = true
  request()
}

/**
 * Fetch and set the table settings.
 */
async function fetchAndSetSettings() {
  const availableSettings = await fetchSettings(props.resourceName, {
    params: commonDataRequestQueryString.value,
  })

  // Set the rules and views if not previously set.
  if (availableSettings.views) {
    views.value = availableSettings.views
  }

  if (availableSettings.rules) {
    availableRules.value = availableSettings.rules
  }

  // We will remove the "views" and "rules", no need in settings.
  settings.value = omit(availableSettings, ['views', 'rules'])
}

async function refetchActions() {
  await fetchActions(props.resourceName, {
    params: commonDataRequestQueryString.value,
  })
}

function setPage(page) {
  collection.currentPage = page
}

function registerReloaders() {
  useGlobalEventListener('floating-resource-updated', () => request())

  useGlobalEventListener('action-executed', ({ resourceName }) => {
    if (resourceName === props.resourceName) request()
  })

  useGlobalEventListener('reload-resource-table', identifier => {
    if (identifier === tableIdentifier.value) request()
  })

  useGlobalEventListener('resource-updated', ({ resourceName }) => {
    if (resourceName === props.resourceName) request()
  })
}

function cancelPreviousRequest() {
  if (!requestCancelToken) return

  requestCancelToken()
  requestCancelToken = null
}

async function applyFilters() {
  if (!initialDataLoaded.value) {
    setDefaults()
  }

  // Wait till Vuex is updated
  await nextTick()

  request()
}

function selectRow(row) {
  row.tSelected = !row.tSelected
}

function unselectAllRows() {
  collection.items.forEach(row => (row.tSelected = false))
}

function selectAllRows() {
  collection.items.forEach(row => (row.tSelected = true))
}

function configureWatchers() {
  if (watchersInitialized === true) return

  watchersInitialized = true

  unwatch.push(
    watch(
      pollingInterval,
      newVal => {
        clearPolling()

        if (newVal) {
          configurePolling()
        }
      },
      { immediate: true }
    )
  )

  unwatch.push(
    watch(
      tableRequestQueryString,
      (newVal, oldVal) => {
        if (!isEqual(newVal, oldVal)) {
          request()
        }
      },
      { deep: true, flush: 'post' }
    )
  )

  unwatch.push(
    watch(perPage, function (newVal) {
      collection.perPage = newVal
    })
  )

  unwatch.push(
    watch(
      defaultOrder,
      (newVal, oldVal) => {
        // Sometimes when fast switching through tables the order is undefined.
        if (newVal && !isEqual(newVal, oldVal)) {
          collection.set('order', newVal)
        }
      },
      {
        deep: true,
      }
    )
  )

  unwatch.push(
    watch(
      () => collection.currentPage,
      newVal => {
        settings.value = {
          ...settings.value,
          currentPage: newVal,
        }
      }
    )
  )
}

function handleViewChanged() {
  // Flush the current data after view it's changed
  // Helps starting with clean table and in case any required columns field
  // does not exists in previous view won't show the red background because the data is not loaded.
  collection.flush()
  setPage(1)
}

function init() {
  registerReloaders()

  fetchAndSetSettings().then(prepareComponent)
}

function onColumnMoveHandler(e) {
  if (
    !withViews.value ||
    !activeView.value ||
    !authorizedToUpdateActiveView.value
  ) {
    return false
  }

  return customizationRef.value.onColumnMove(e)
}

function performCleanup() {
  cancelPreviousRequest()
  unwatch.forEach(func => func())
  unwatch = []
  collection.flush()
  clearPolling()
  setLoading(false)
}

onMounted(init)
onBeforeUnmount(performCleanup)

defineExpose({
  refetchActions,
  setPage,
})
</script>

<style>
th.sortable-chosen.sortable-drag,
th.sortable-chosen.sortable-column-ghost {
  @apply ring-1 ring-inset ring-neutral-400 dark:ring-neutral-600;
}
</style>
