<template>
  <ITableRow
    :aria-selected="row.tSelected"
    :class="[
      'group/tr',
      row._row_class,
      row._row_border
        ? '[&>td:first-child]:before:absolute [&>td:first-child]:before:left-0 [&>td:first-child]:before:top-0 [&>td:first-child]:before:h-full [&>td:first-child]:before:w-auto [&>td:first-child]:before:border-l-2 [&>td:first-child]:before:border-transparent'
        : '',
      row._row_border
        ? {
            '[&>td:first-child]:before:!border-warning-500':
              row._row_border === 'warning',
            '[&>td:first-child]:before:!border-danger-500':
              row._row_border === 'danger',
            '[&>td:first-child]:before:!border-success-500':
              row._row_border === 'success',
            '[&>td:first-child]:before:!border-info-500':
              row._row_border === 'info',
            '[&>td:first-child]:before:!border-primary-500':
              row._row_border === 'primary',
          }
        : '',
    ]"
    @click="selectOnRowClick"
  >
    <ResourceTableCell
      v-for="(column, cidx) in columns"
      :key="'td-' + column.attribute"
      :attribute="column.attribute"
      :wrap="column.wrap"
      :condensed="isCondensed"
      :has-required-field="column.field ? column.field.isRequired : false"
      :align="column.align"
      :newlineable="column.newlineable"
      :link="column.link"
      :route="column.route"
      :class="column.tdClass"
      :is-primary="column.primary"
      :is-selected="row.tSelected || false"
      :is-selectable="isSelectable && cidx === 0"
      :row="row"
      @selected="selectRow"
    >
      <slot
        v-bind="{ column, row, resourceName, resourceId: row.id }"
        :name="column.attribute"
      >
        <template v-if="column.attribute === 'actions'">
          <ResourceTableRowActions
            :actions="inlineActions"
            :resource-name="resourceName"
            :resource-id="row.id"
            :additional-request-params="runActionRequestAdditionalParams"
            @action-executed="$emit('actionExecuted', $event)"
          />
        </template>

        <template
          v-else-if="!column.component && !column.field?.indexComponent"
        >
          {{ row[column.attribute] }}
        </template>

        <component
          :is="column.component || column.field.indexComponent"
          v-else
          :field="
            column.field
              ? {
                  ...column.field,
                  value: row[column.attribute],
                }
              : undefined
          "
          v-bind="{ column, row, resourceName, resourceId: row.id }"
          @reload="$emit('reload')"
        />
      </slot>
    </ResourceTableCell>
  </ITableRow>
</template>

<script setup>
import { computed } from 'vue'

import ResourceTableCell from './ResourceTableCell.vue'
import ResourceTableRowActions from './ResourceTableRowActions.vue'

const props = defineProps({
  row: { type: Object, required: true },
  resourceName: { type: String, required: true },
  columns: { type: Object, required: true },
  isSelectable: { type: Boolean, required: true },
  isCondensed: { type: Boolean, required: true },
  selectedRowsCount: { type: Number, required: true },
  actions: { type: Object, required: true },
  runActionRequestAdditionalParams: { type: Object, required: true },
})

const emit = defineEmits(['reload', 'selected', 'actionExecuted'])

const inlineActions = computed(() =>
  props.actions.filter(action => action.showInline === true)
)

function selectRow() {
  emit('selected')
}

function selectOnRowClick(e) {
  // Auto selecting works only if the table is selectable and there is at least one row selected.
  if (!props.isSelectable || props.selectedRowsCount === 0) {
    return
  }

  const nonSelectableTags = ['INPUT', 'SELECT', 'TEXTAREA', 'A', 'BUTTON']

  if (
    nonSelectableTags.includes(e.target.tagName) ||
    e.target.isContentEditable
  ) {
    return
  }

  selectRow()
}
</script>
