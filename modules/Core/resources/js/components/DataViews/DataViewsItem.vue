<template>
  <div
    v-bind="$attrs"
    :class="[
      'group relative flex cursor-move items-center',
      'bg-neutral-100 dark:bg-neutral-900 dark:before:absolute dark:before:inset-0',
      active
        ? 'bg-white dark:before:bg-neutral-700/60'
        : 'hover:bg-white dark:before:bg-neutral-500/10 dark:hover:bg-neutral-700/60',
    ]"
  >
    <button
      type="button"
      :title="view.name"
      :class="[
        'z-10 inline-flex grow items-center gap-x-1.5 truncate border-0 px-6 py-2 text-center text-base text-neutral-800 hover:text-neutral-900 focus:outline-none dark:text-neutral-200 sm:text-sm',
        active && 'font-semibold',
      ]"
      @dblclick="canUpdate ? (showEditModal = true) : undefined"
      @click="selectView"
    >
      <Icon
        v-show="!isExceedingMaxOpenViews"
        icon="Selector"
        class="absolute left-1.5 h-4 w-4 opacity-0 group-hover:opacity-100"
      />

      <Icon
        v-if="isViewExceedingMaxOpenViews"
        v-i-tooltip.bottom.light="$t('core::data_views.open_views_limit_hit')"
        icon="ExclamationTriangle"
        class="h-5 w-5 shrink-0 text-warning-500"
      />

      <span class="truncate">
        {{ view.name }}
      </span>
    </button>

    <IDropdownMinimal
      class="pointer-events-none mr-2 opacity-0 group-hover:pointer-events-auto group-hover:opacity-100"
      small
    >
      <IDropdownItem v-if="canUpdate" @click="showEditModal = true">
        <Icon icon="PencilAlt" />
        {{ $t('core::app.edit') }}
      </IDropdownItem>

      <IDropdownItem v-if="totalOpen !== 1" @click="$emit('closed', view.id)">
        <Icon icon="XSolid" />

        <IDropdownItemLabel>
          {{ $t('core::app.close') }}
        </IDropdownItemLabel>

        <IDropdownItemDescription>
          {{ $t('core::data_views.mark_as_default_for_current_account_only') }}
        </IDropdownItemDescription>
      </IDropdownItem>

      <IDropdownItem @click="$emit('clone', view.id)">
        <Icon icon="Duplicate" />
        {{ $t('core::data_views.clone') }}
      </IDropdownItem>

      <IDropdownItem v-if="canDelete" confirmable @click="destroy">
        <Icon icon="Trash" />
        {{ $t('core::app.delete') }}
      </IDropdownItem>
    </IDropdownMinimal>
  </div>

  <DataViewsEdit
    v-model:visible="showEditModal"
    :view="view"
    :identifier="identifier"
    @updated="handleViewUpdated"
  />
</template>

<script setup>
import { computed, ref } from 'vue'

import { useDataViews } from '@/Core/composables/useDataViews'

import DataViewsEdit from './DataViewsEdit.vue'

defineOptions({ inheritAttrs: false })

const props = defineProps({
  view: { type: Object, required: true },
  identifier: { type: String, required: true },
  isViewExceedingMaxOpenViews: { type: Boolean, required: true },
  isExceedingMaxOpenViews: { type: Boolean, required: true },
  active: Boolean,
  totalOpen: { type: Number, required: true },
})

const emit = defineEmits(['selected', 'closed', 'clone', 'updated', 'deleted'])

const { activeView } = useDataViews(props.identifier)

const showEditModal = ref(false)

const canUpdate = computed(
  () => props.view.authorizations.update && !props.view.is_system_default
)

const canDelete = computed(
  () =>
    props.totalOpen !== 1 &&
    props.view.authorizations.delete &&
    !props.view.is_system_default
)

async function destroy() {
  await Innoclapps.request().delete(`/views/${props.view.id}`)

  emit('deleted', props.view.id)
}

function selectView() {
  emit('selected', props.view.id)
  activeView.value = props.view.id
}

function handleViewUpdated(view) {
  emit('updated', view)
  showEditModal.value = false
}
</script>
