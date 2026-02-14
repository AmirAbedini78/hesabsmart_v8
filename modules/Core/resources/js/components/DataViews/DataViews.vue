<template>
  <div class="block sm:hidden">
    <ICustomSelect
      label="name"
      :options="currentOpenViews"
      :model-value="activeView"
      @update:model-value="selectView"
    />
  </div>

  <div
    class="-mx-[--gutter] hidden border-b border-neutral-300 px-[--gutter] dark:border-white/10 sm:block"
    v-bind="$attrs"
  >
    <div class="flex items-center gap-x-2">
      <SortableDraggable
        v-model="currentOpenViews"
        tag="div"
        class="grid auto-cols-[minmax(100px,400px)] grid-flow-col grid-cols-[repeat(auto-fill,minmax(100px,400px))] grid-rows-[minmax(38px,1fr)]"
        item-key="id"
        v-bind="{
          ...$draggable.common,
          filter: '.draggable-exclude',
          preventOnFilter: false,
          ghostClass: 'drag-ghost',
        }"
      >
        <template #item="{ element: view, index }">
          <div
            :class="[
              'relative overflow-hidden border-t border-neutral-300 first:rounded-tl-lg first:border-l odd:border-r even:border-r dark:border-white/10',
              index === totalOpenViews - 1 ? 'rounded-tr-lg' : '',
              totalOpenViews > MAX_OPEN_VIEWS && 'draggable-exclude',
            ]"
          >
            <DataViewsItem
              :view="view"
              :active="activeView && view.id === activeView.id"
              :is-view-exceeding-max-open-views="index >= MAX_OPEN_VIEWS"
              :is-exceeding-max-open-views="exceedingMaxOpenViews"
              :identifier="identifier"
              :total-open="totalOpenViews"
              @deleted="handleViewDeleted"
              @selected="$emit('selected', $event)"
              @clone="cloneView"
              @closed="handleViewClosed"
            />
          </div>
        </template>
      </SortableDraggable>

      <div class="ml-3 flex shrink-0 items-center space-x-2">
        <IPopover placement="bottom-end" :offset="15">
          <IPopoverButton class="font-medium" link basic>
            &plus; {{ $t('core::data_views.add_view') }} ({{
              totalOpenViews
            }}/{{ MAX_OPEN_VIEWS }})
          </IPopoverButton>

          <IPopoverPanel class="w-[28rem]">
            <ITabGroup>
              <ITabList centered>
                <ITab v-if="systemViews.length">
                  <span
                    class="max-w-32 truncate"
                    v-text="$t('core::data_views.default_views')"
                  />
                </ITab>

                <ITab>
                  <span
                    class="max-w-32 truncate"
                    v-text="$t('core::data_views.created_by_me')"
                  />
                </ITab>

                <ITab>
                  <span
                    class="max-w-32 truncate"
                    v-text="$t('core::data_views.created_by_others')"
                  />
                </ITab>
              </ITabList>

              <ITabPanels>
                <ITabPanel v-if="systemViews.length">
                  <DataViewsList :views="systemViews" @selected="selectView" />
                </ITabPanel>

                <ITabPanel>
                  <DataViewsList
                    :views="createdByCurrentUser"
                    @selected="selectView"
                  />
                </ITabPanel>

                <ITabPanel>
                  <DataViewsList
                    :views="createdByOthers"
                    @selected="selectView"
                  />
                </ITabPanel>
              </ITabPanels>
            </ITabGroup>

            <div class="border-t border-neutral-200 dark:border-neutral-500/30">
              <div class="px-4 py-2">
                <ILink @click="viewBeingCreated = true">
                  &plus; {{ $t('core::data_views.create_new') }}
                </ILink>
              </div>
            </div>
          </IPopoverPanel>
        </IPopover>
      </div>
    </div>

    <DataViewsCreate
      v-model:visible="viewBeingCreated"
      :identifier="identifier"
      @created="handleViewCreated"
    />
  </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'

import { useApp } from '@/Core/composables/useApp'
import { useDataViews } from '@/Core/composables/useDataViews'

import DataViewsCreate from './DataViewsCreate.vue'
import DataViewsItem from './DataViewsItem.vue'
import DataViewsList from './DataViewsList.vue'

defineOptions({ inheritAttrs: false })

const props = defineProps({
  identifier: { type: String, required: true },
  singleViewOnly: Boolean,
  activeViewId: Number,
})

const emit = defineEmits(['created', 'selected', 'deleted', 'changed'])

const router = useRouter()
const route = useRoute()
const { currentUser, scriptConfig } = useApp()

const {
  views,
  activeView,
  patchView,
  addView,
  findView,
  removeView,
  firstOpenView,
} = useDataViews(props.identifier)

const MAX_OPEN_VIEWS = scriptConfig('views.max_open')

const viewBeingCreated = ref(false)
const extraSelectedViewId = ref(null)

const viewsAreNotCustomized = computed(() =>
  views.value.every(view => view.is_open_for_user === null)
)

const userOpenedViews = computed(() =>
  viewsAreNotCustomized.value
    ? views.value.slice(0, MAX_OPEN_VIEWS)
    : views.value.filter(view => view.is_open_for_user)
)

const currentOpenViews = computed({
  get() {
    if (!extraSelectedViewId.value) {
      return userOpenedViews.value
    }

    return userOpenedViews.value.concat(findView(extraSelectedViewId.value))
  },
  set(newValue) {
    updateViewsOrder(newValue)
  },
})

const totalOpenViews = computed(() => currentOpenViews.value.length)

const exceedingMaxOpenViews = computed(
  () => currentOpenViews.value.length > MAX_OPEN_VIEWS
)

const systemViews = computed(() =>
  views.value.filter(view => view.is_system_default)
)

const createdByOthers = computed(() =>
  views.value.filter(view => view.is_shared_from_another_user)
)

const createdByCurrentUser = computed(() =>
  views.value.filter(view => view.user_id === currentUser.value.id)
)

watch(
  () => route.query.view_id,
  newVal => {
    const viewToSelect =
      findView(newVal) ||
      activeView.value ||
      firstOpenView.value ||
      views.value[0]

    selectView(viewToSelect, false)

    updateRouteQueryParams(viewToSelect?.id)
  },
  { immediate: true }
)

watch(
  () => activeView.value?.id,
  newVal => {
    updateRouteQueryParams(newVal)
    emit('changed', newVal)
  }
)

function updateRouteQueryParams(viewId) {
  if (!props.singleViewOnly) {
    router.replace({
      query: { ...route.query, view_id: viewId },
    })
  }
}

function createOrderBulkUpdatePayload(views) {
  return views.reduce((acc, view, index) => {
    acc[view.id] = index + 1

    return acc
  }, {})
}

function createIsOpenBulkUpdatePayload(views, isOpen) {
  return views.reduce((acc, view) => {
    acc[view.id] = isOpen

    return acc
  }, {})
}

async function updateViewsOrder(updatedViews) {
  const payload = createOrderBulkUpdatePayload(updatedViews)

  // First update the view in store so it's immediately reflected on the UI.
  Object.keys(payload).forEach(viewId => {
    patchView({
      ...findView(viewId),
      user_order: payload[viewId],
    })
  })

  Innoclapps.request().post(`views/${props.identifier}/config/order`, payload)
}

async function syncCurrentViewsOpenState(override = {}) {
  let syncedPayload = {
    // Set all views as closed
    ...createIsOpenBulkUpdatePayload(views.value, false),
    // Override from open views
    ...createIsOpenBulkUpdatePayload(currentOpenViews.value, true),
    // Add any override config
    ...override,
  }

  // First update the view in store so it's immediately reflected on the UI.
  Object.keys(syncedPayload).forEach(viewId => {
    patchView({
      ...findView(viewId),
      is_open_for_user: syncedPayload[viewId],
    })
  })

  Innoclapps.request().post(
    `views/${props.identifier}/config/open`,
    syncedPayload
  )
}

function handleViewDeleted(id) {
  if (parseInt(id) === parseInt(extraSelectedViewId.value)) {
    extraSelectedViewId.value = null
  }

  if (parseInt(activeView.value.id) === parseInt(id)) {
    activeView.value = currentOpenViews.value[0].id
  }

  removeView(id)

  emit('deleted', id)
}

async function handleViewClosed(id) {
  if (parseInt(id) === parseInt(extraSelectedViewId.value)) {
    extraSelectedViewId.value = null
  } else {
    // When a view is closed and exceeding the max views and there is an extra view
    // we will set this extra to view as open, as the user closed another view.
    if (exceedingMaxOpenViews.value) {
      await syncCurrentViewsOpenState({
        [extraSelectedViewId.value]: true,
        [id]: false,
      })
      extraSelectedViewId.value = null
    } else {
      await syncCurrentViewsOpenState({
        [id]: false,
      })
    }
  }

  if (parseInt(activeView.value.id) === parseInt(id)) {
    activeView.value = currentOpenViews.value[0].id
  }
}

function selectView(view, sync = true) {
  const viewId = typeof view === 'object' ? view.id : view

  const alreadyOpen = Boolean(
    currentOpenViews.value.find(openView => openView.id == viewId)
  )

  if (!alreadyOpen) {
    if (totalOpenViews.value >= MAX_OPEN_VIEWS) {
      extraSelectedViewId.value = viewId
    } else if (sync === true) {
      syncCurrentViewsOpenState({ [viewId]: true })
    } else {
      extraSelectedViewId.value = viewId
    }
  }

  activeView.value = viewId
}

async function handleViewCreated(view) {
  selectView(view)
  viewBeingCreated.value = false
  emit('created', view)
}

async function cloneView(id) {
  const view = findView(id)

  const { data } = await Innoclapps.request().post('/views', {
    name: view.name + ' Copy',
    identifier: view.identifier,
    config: view.config,
    rules: view.rules,
    // Ensure it's always false so in case the view has rules with authorization.
    is_shared: false,
  })

  addView(data)

  handleViewCreated(data)
}
</script>
