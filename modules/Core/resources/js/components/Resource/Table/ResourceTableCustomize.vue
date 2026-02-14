<template>
  <IModal
    :id="identifier + 'listSettings'"
    size="sm"
    :visible="tableBeingCustomized"
    :title="
      tableSettings.singleViewOnly
        ? $t('core::table.table_settings')
        : $t('core::data_views.view_settings')
    "
    @hidden="handleModalHidden"
  >
    <div v-if="tableSettings.allowDefaultSortChange" class="mb-4 mt-10">
      <ITextDark
        class="mb-1 font-medium"
        :text="$t('core::table.default_sort')"
      />

      <SortableDraggable
        v-model="defaultSort"
        class="space-y-2"
        handle="[data-sortable-handle='table-customize-sorting']"
        :item-key="item => item.attribute + '-' + item.direction"
        v-bind="$draggable.common"
      >
        <template #item="{ index }">
          <div class="flex items-center space-x-1.5">
            <div class="grow">
              <IFormSelect
                :id="'column_' + index"
                v-model="defaultSort[index].attribute"
              >
                <!-- ios by default selects the first field but no events are triggered in this case
                    we will make sure to add blank one -->
                <option v-if="!defaultSort[index].attribute" value=""></option>

                <option
                  v-for="sortableColumn in sortableColumns"
                  v-show="!isSortedColumnDisabled(sortableColumn.attribute)"
                  :key="sortableColumn.attribute"
                  :value="sortableColumn.attribute"
                  v-text="sortableColumn.label"
                />
              </IFormSelect>
            </div>

            <div class="flex-auto">
              <IFormSelect
                :id="'column_type_' + index"
                v-model="defaultSort[index].direction"
              >
                <option value="asc">
                  Asc ({{ $t('core::app.ascending') }})
                </option>

                <option value="desc">
                  Desc ({{ $t('core::app.descending') }})
                </option>
              </IFormSelect>
            </div>

            <IButton
              :variant="index === 0 ? 'secondary' : 'danger'"
              :disabled="index === 0 && isAddSortColumnDisabled"
              :soft="index > 0"
              :icon="index === 0 ? 'PlusSolid' : 'MinusSolid'"
              @click="
                index === 0
                  ? addDefaultSortColumn()
                  : removeDefaultSortColumn(index)
              "
            />

            <div
              data-sortable-handle="table-customize-sorting"
              class="cursor-move"
            >
              <Icon class="size-5 text-neutral-500" icon="Selector" />
            </div>
          </div>
        </template>
      </SortableDraggable>
    </div>

    <IFormLabel
      class="mb-1 font-medium"
      for="search-table-columns"
      :label="$t('core::table.columns')"
    />

    <InputSearch
      v-if="columnsConfig.length > SHOW_SEARCH_INPUT_WHEN_COLUMNS_ARE_MORE_THAN"
      id="search-table-columns"
      v-model="search"
      class="mb-4"
      @update:model-value="createLocalColumnsConfig"
    />

    <div class="mb-4 max-h-[400px] overflow-auto">
      <SortableDraggable
        v-model="columnsConfig"
        item-key="attribute"
        :move="onColumnMove"
        v-bind="{
          ...$draggable.scrollable,
          filter: '.visibility-checkbox, .visibility-checkbox-label',
        }"
      >
        <template #item="{ element, index }">
          <div
            v-show="element._searchMatch"
            v-i-tooltip="
              element.primary ? $t('core::table.primary_column') : ''
            "
            :class="[
              'mb-1.5 me-2 flex items-center rounded-md border border-neutral-200 px-3 py-2 dark:border-neutral-500/30',
              element.primary
                ? 'bg-neutral-50 dark:bg-neutral-800'
                : 'hover:bg-neutral-50 dark:hover:bg-neutral-800',
            ]"
          >
            <div class="grow">
              <IFormCheckboxField>
                <IFormCheckbox
                  v-model:checked="columnsConfig[index].visible"
                  class="visibility-checkbox"
                  :disabled="
                    !element.canToggleVisibility || element.primary === true
                  "
                />

                <IFormCheckboxLabel
                  class="visibility-checkbox-label inline-flex items-center space-x-1"
                >
                  <Icon
                    v-if="element.helpText"
                    v-i-tooltip="element.helpText"
                    icon="QuestionMarkCircle"
                    class="size-4 text-neutral-600"
                  />

                  <span>
                    {{ element.label }}
                  </span>
                </IFormCheckboxLabel>
              </IFormCheckboxField>
            </div>

            <Icon
              v-if="!element.primary && tableSettings.reorderable"
              v-show="!search"
              class="size-5 cursor-move text-neutral-500"
              icon="Selector"
            />
          </div>
        </template>
      </SortableDraggable>
    </div>

    <IFormGroup
      label-for="tableSettingsPerPage"
      :label="$t('core::table.per_page')"
    >
      <IFormSelect id="tableSettingsPerPage" v-model="perPage">
        <option v-for="number in [25, 50, 100]" :key="number" :value="number">
          {{ number }}
        </option>
      </IFormSelect>
    </IFormGroup>

    <IFormGroup
      label-for="tableSettingsMaxHeight"
      :label="$t('core::table.max_height')"
      :description="$t('core::table.max_height_info')"
    >
      <div class="relative mt-1 rounded-md shadow-sm">
        <IFormInput
          id="tableSettingsMaxHeight"
          v-model="maxHeight"
          type="number"
          min="200"
          step="50"
          class="pe-8 sm:pe-10 [&::-webkit-calendar-picker-indicator]:opacity-0"
          list="maxHeight"
        />

        <datalist id="maxHeight">
          <option value="200" />

          <option value="250" />

          <option value="300" />

          <option value="350" />

          <option value="400" />

          <option value="500" />
        </datalist>

        <div
          class="pointer-events-none absolute inset-y-0 end-0 flex items-center pe-3"
        >
          <span class="-mt-1 text-neutral-400">px</span>
        </div>
      </div>
    </IFormGroup>

    <div class="mt-5">
      <IFormCheckboxField>
        <IFormCheckbox v-model:checked="bordered" />

        <IFormCheckboxLabel :text="$t('core::table.bordered')" />
      </IFormCheckboxField>

      <IFormCheckboxField>
        <IFormCheckbox v-model:checked="condensed" />

        <IFormCheckboxLabel :text="$t('core::table.condensed')" />
      </IFormCheckboxField>

      <IFormGroup
        :description="pollingEnabled ? $t('core::table.polling_info') : null"
      >
        <IFormCheckboxField>
          <IFormCheckbox
            v-model:checked="pollingEnabled"
            @change="pollingInterval = $event ? DEFAULT_POLLING_INTERVAL : null"
          />

          <IFormCheckboxLabel :text="$t('core::table.enable_polling')" />
        </IFormCheckboxField>

        <IFormInput
          v-show="pollingEnabled"
          v-model="pollingInterval"
          type="number"
          min="10"
          class="mt-2"
          @blur="
            pollingInterval < MINIMUM_POLLING_INTERVAL
              ? (pollingInterval = MINIMUM_POLLING_INTERVAL)
              : undefined
          "
        />
      </IFormGroup>
    </div>

    <template #modal-footer>
      <div class="flex items-center justify-between text-end">
        <IButton
          v-show="activeView.is_system_default || tableSettings.singleViewOnly"
          :disabled="form.busy"
          :text="$t('core::app.reset')"
          :confirm-text="$t('core::app.confirm')"
          basic
          confirmable
          @confirmed="reset"
        />

        <div class="ms-auto space-x-2">
          <IButton
            :disabled="form.busy"
            :text="$t('core::app.cancel')"
            basic
            @click="tableBeingCustomized = false"
          />

          <IButton
            variant="primary"
            :disabled="form.busy"
            :text="$t('core::app.save')"
            @click="save()"
          />
        </div>
      </div>
    </template>
  </IModal>
</template>

<script setup>
import { computed, nextTick, ref, watch } from 'vue'
import cloneDeep from 'lodash/cloneDeep'
import filter from 'lodash/filter'
import find from 'lodash/find'
import isNil from 'lodash/isNil'
import keyBy from 'lodash/keyBy'
import sortBy from 'lodash/sortBy'

import { useDataViews } from '../../../composables/useDataViews'
import { useForm } from '../../../composables/useForm'
import { useTable } from '../../../composables/useTable'

const props = defineProps({
  identifier: { required: true, type: String },
  cacheKey: {
    required: true,
    validator: value => typeof value === 'string' || isNil(value),
  },
})

const { activeView, patchView } = useDataViews(props.identifier)

const { settings: tableSettings, reloadTable } = useTable(
  props.identifier,
  () => props.cacheKey
)

const tableBeingCustomized = ref(false)
const columnsConfig = ref({})
const defaultSort = ref([])
const search = ref(null)
const maxHeight = ref(null)
const condensed = ref(false)
const bordered = ref(false)
const perPage = ref(null)
const pollingEnabled = ref(false)
const MINIMUM_POLLING_INTERVAL = tableSettings.value.minimumPollingInterval
const DEFAULT_POLLING_INTERVAL = 25
const SHOW_SEARCH_INPUT_WHEN_COLUMNS_ARE_MORE_THAN = 5
const pollingInterval = ref(null)

const { form } = useForm()

const customizeableColumns = computed(() =>
  filter(tableSettings.value.columns, col => col.attribute !== 'actions')
)

const sortableColumns = computed(() =>
  filter(customizeableColumns.value, 'sortable')
)

const isAddSortColumnDisabled = computed(() => {
  // Return true if all sortable columns are already sorted
  if (defaultSort.value.length === sortableColumns.value.length) return true

  // Check if any sorted column has not been selected (has an empty 'attribute')
  return defaultSort.value.some(column => column.attribute === '')
})

function onColumnMove(data) {
  // You can't reorder primary columns or actions column
  // you can't add new columns before the first primary column
  // as the first primary column contains specific data table related to the table
  // You can't add new columns after the last primary column
  const { index, futureIndex } = data.draggedContext
  const isPrimaryColumn = idx => columnsConfig.value[idx].primary

  if (!tableSettings.value.reorderable) {
    return false
  }

  if (
    isPrimaryColumn(index) ||
    (futureIndex === 0 && isPrimaryColumn(futureIndex))
  ) {
    return false
  }
}

function isSortedColumnDisabled(attribute) {
  return Boolean(find(defaultSort.value, ['attribute', attribute]))
}

function addDefaultSortColumn() {
  defaultSort.value.push({ attribute: '', direction: 'asc' })
}

function removeDefaultSortColumn(index) {
  defaultSort.value.splice(index, 1)
}

function handleModalHidden() {
  if (tableBeingCustomized.value) {
    tableBeingCustomized.value = false
  }

  search.value = null
}

function createLocalColumnsConfig() {
  const columnsMap = keyBy(customizeableColumns.value, 'attribute')

  const makeAttributes = config => {
    const column = columnsMap[config.attribute]

    return {
      label: column.label,
      primary: column.primary,
      canToggleVisibility: column.canToggleVisibility,
      attribute: config.attribute,
      order: config.order,
      width: config.width,
      wrap: config.wrap,
      visible: !config.hidden,
      _searchMatch: !search.value
        ? true
        : column.label.toLowerCase().includes(search.value.toLowerCase()),
    }
  }

  let config = activeView.value.config.table.columns
    // Remove any non existing columns stored in the view config.
    .filter(config => Object.hasOwn(columnsMap, config.attribute))
    // Create local config with additional values.
    .map(makeAttributes)

  // Add any new columns that not exists in the view config.
  config = config.concat(
    filter(
      customizeableColumns.value,
      col => !find(config, ['attribute', col.attribute])
    ).map(makeAttributes)
  )

  // Finally, make sure they are properly sorted
  columnsConfig.value = sortBy(config, 'order')
}

function reset() {
  saveView(
    form.clear().set({
      config: {
        ...activeView.value.config,
        table: {
          ...tableSettings.value.defaults,
          columns: prepareColumnsForStorage(
            customizeableColumns.value.map(col => ({
              ...col,
              order: col.order,
              visible: !col.hidden,
            }))
          ),
        },
      },
    })
  ).then(initializeComponent)
}

function save(columns, reload = true) {
  return saveView(
    form.clear().set({
      config: {
        ...activeView.value.config,
        table: {
          order: defaultSort.value.filter(column => column.attribute !== ''),
          columns: prepareColumnsForStorage(columns || columnsConfig.value),
          pollingInterval: pollingInterval.value,
          maxHeight: maxHeight.value,
          condensed: condensed.value,
          bordered: bordered.value,
          perPage: perPage.value,
        },
      },
    }),
    reload
  )
}

async function saveView(form, reload = true) {
  const updatedView = await form.put(`views/${activeView.value.id}`)

  patchView(updatedView)

  search.value = ''

  await nextTick()
  populateLocalVariables()

  // Reload table to fetch new columns data.
  if (reload) {
    await nextTick()
    reloadTable()
  }

  tableBeingCustomized.value = false
}

function prepareColumnsForStorage(newColumns) {
  // Create a lookup map for newColumns based on the 'attribute'
  const newColumnsOrderMap = new Map(
    newColumns.map((col, index) => [col.attribute, index + 1])
  )

  // Clone the original columnsConfig to avoid direct mutation
  let sortedColumnsConfig = [...cloneDeep(columnsConfig.value)]

  // Only sort the columns that are present in newColumns
  sortedColumnsConfig = sortedColumnsConfig.map(col => {
    const colOrder = newColumnsOrderMap.get(col.attribute)

    return {
      ...col,
      // Use the index from newColumns if it exists, otherwise use the original order
      order: colOrder !== undefined ? colOrder : col.order,
    }
  })

  // Now sort by the new 'order', keeping original position for items not in newColumns
  sortedColumnsConfig.sort((a, b) => a.order - b.order)

  // Now, map the sorted array to apply any new settings from newColumns
  return sortedColumnsConfig.map((col, index) => {
    const newColSettings =
      newColumns.find(nc => nc.attribute === col.attribute) || {}

    return {
      attribute: col.attribute,
      order: index + 1,
      width:
        newColSettings.width !== undefined ? newColSettings.width : col.width,
      wrap: newColSettings.wrap !== undefined ? newColSettings.wrap : col.wrap,
      hidden: Object.hasOwn(newColSettings, 'visible')
        ? !newColSettings.visible
        : Object.hasOwn(newColSettings, 'hidden')
          ? newColSettings.hidden
          : true,
    }
  })
}

function populateLocalVariables() {
  pollingInterval.value = activeView.value.config.table.pollingInterval

  pollingEnabled.value =
    parseInt(activeView.value.config.table.pollingInterval) >=
    MINIMUM_POLLING_INTERVAL

  maxHeight.value = activeView.value.config.table.maxHeight
  condensed.value = activeView.value.config.table.condensed
  bordered.value = activeView.value.config.table.bordered
  perPage.value = activeView.value.config.table.perPage

  createLocalColumnsConfig()
}

function initializeComponent() {
  populateLocalVariables()

  if (activeView.value.config.table.order.length) {
    defaultSort.value = cloneDeep(activeView.value.config.table.order)
  } else {
    addDefaultSortColumn()
  }
}

function setVisibility(value) {
  tableBeingCustomized.value = value
}

watch(
  () => activeView.value?.id,
  newVal => {
    if (newVal) {
      initializeComponent()
    }
  },
  { immediate: true }
)

defineExpose({ save, onColumnMove, setVisibility })
</script>
