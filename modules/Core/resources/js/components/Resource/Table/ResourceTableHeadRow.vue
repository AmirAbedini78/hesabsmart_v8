<template>
  <SortableDraggable
    tag="tr"
    :model-value="columns"
    :class="
      isSticky
        ? '[&>th]:sticky [&>th]:top-0 [&>th]:bg-opacity-75 [&>th]:backdrop-blur-sm [&>th]:backdrop-filter'
        : ''
    "
    :item-key="item => 'th' + item.attribute"
    v-bind="{
      ...$draggable.scrollable,
      delay: 100,
      ghostClass: 'sortable-column-ghost',
      filter: '.resizer, .draggable-exclude',
      preventOnFilter: false,
    }"
    @update:model-value="$emit('update:columns', $event)"
  >
    <template #item="{ element, index: idx }">
      <ResourceTableHeader
        :attribute="element.attribute"
        :class="[
          element.thClass,
          !withViews ||
          !reorderable ||
          element.attribute === 'actions' ||
          !authorizedToUpdateActiveView
            ? 'draggable-exclude'
            : '',
        ]"
        :wrap="element.wrap"
        :with-views="withViews"
        :authorized-to-update-view="authorizedToUpdateActiveView"
        :can-toggle-visibility="
          withViews &&
          authorizedToUpdateActiveView &&
          element.canToggleVisibility
        "
        :label="element.label"
        :align="element.align"
        :condensed="isCondensed"
        :is-selectable="isSelectable && idx === 0"
        :is-sortable="element.sortable"
        :is-primary="element.primary"
        :is-ordered="isOrderedByCallback(element)"
        :is-sorted-ascending="isSortedAscendingCallback(element)"
        :width="element.width || 'auto'"
        :total-selected="selectedRowsCount"
        :all-rows-selected="allRowsSelected"
        @customize="$emit('customizeViewRequested')"
        @sort-asc="$emit('sortAsc', $event)"
        @sort-desc="$emit('sortDesc', $event)"
        @checkbox-changed="handleCheckboxChanged"
        @updated="handleColumnUpdated(idx, $event)"
      />
    </template>
  </SortableDraggable>
</template>

<script setup>
import ResourceTableHeader from './ResourceTableHeader.vue'

const props = defineProps({
  columns: { type: Object, required: true },
  isSticky: { type: Boolean, required: true },
  isSelectable: { type: Boolean, required: true },
  isCondensed: { type: Boolean, required: true },
  selectedRowsCount: { type: Number, required: true },
  allRowsSelected: { type: Boolean, required: true },
  withViews: { type: Boolean, required: true },
  authorizedToUpdateActiveView: { type: Boolean, required: true },
  reorderable: { type: Boolean, required: true },
  isOrderedByCallback: { type: Function, required: true },
  isSortedAscendingCallback: { type: Function, required: true },
})

const emit = defineEmits([
  'update:columns',
  'selectAll',
  'unselectAll',
  'sortAsc',
  'sortDesc',
  'customizeViewRequested',
])

function handleColumnUpdated(index, settings) {
  const updatedColumns = [...props.columns]
  updatedColumns[index] = { ...updatedColumns[index], ...settings }

  emit('update:columns', updatedColumns)
}

function handleCheckboxChanged(isIndeterminate) {
  if (isIndeterminate || props.allRowsSelected) {
    emit('unselectAll')
  } else {
    emit('selectAll')
  }
}
</script>
