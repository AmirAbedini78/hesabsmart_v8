<template>
  <MainLayout :overlay="isLoading" no-padding>
    <template #actions>
      <NavbarSeparator class="hidden lg:block" />

      <NavbarItems>
        <DealsNavbarViewSelector active="board" />

        <IButton
          variant="primary"
          icon="PlusSolid"
          :text="$t('deals::deal.create')"
          @click="createDealRequested"
        />
      </NavbarItems>
    </template>

    <BaseBoard
      board-class="deals-board"
      class="[--board-top-height:112px]"
      :board-id="resourceName"
    >
      <template #top>
        <DataViews
          v-if="componentReady"
          class="mb-3 [--gutter:theme(spacing.4)] sm:[--gutter:theme(spacing.6)]"
          :identifier="viewsIdentifier"
        />

        <div class="sm:flex sm:flex-wrap sm:items-center">
          <InputSearch
            v-model="query.q"
            :class="['w-full sm:w-auto', !componentReady ? 'blur' : '']"
            :placeholder="$t('core::app.search')"
            :disabled="hasRules && !rulesAreValid"
            @update:model-value="fetchAsNew"
          />

          <div
            v-if="componentReady && hasRules"
            class="ml-0 mt-1 space-y-2 sm:ml-4 sm:mt-0 md:flex md:items-center md:space-x-3.5 md:space-y-0"
          >
            <DataViewFilters
              :identifier="viewsIdentifier"
              @apply="fetchAsNew"
            />
          </div>

          <div
            class="mt-4 flex w-full flex-wrap justify-between sm:ml-auto sm:mt-0 sm:w-auto md:justify-end"
          >
            <div
              :class="[
                'inline-flex items-center space-x-2',
                !componentReady ? 'blur' : '',
              ]"
            >
              <div class="w-52">
                <DealsPipelinesDropdown
                  @activated="activePipelineId = $event"
                  @changed="activePipelineId = $event"
                />
              </div>

              <IButton
                v-i-tooltip="$t('core::app.sort')"
                v-dialog="'boardSortModal'"
                variant="secondary"
                icon="SortAscending"
              />

              <DealsBoardSortOptions @applied="handleSortApplied" />
            </div>
          </div>
        </div>
      </template>

      <BaseBoardColumn
        v-for="[stageId, column] in stages"
        :key="column.name"
        v-model="column.cards"
        :name="column.name"
        :column-id="stageId"
        :move="onMoveCallback"
        :loader="loader"
        :board-id="resourceName"
        @drag-start="showBottomDropper = true"
        @drag-end="showBottomDropper = false"
        @update:model-value="handleColumnCardsUpdated"
        @updated="handleColumnUpdatedEvent"
      >
        <template #after-header>
          <div class="-mt-1 flex items-center text-sm">
            <span
              v-if="
                column.win_probability === 100 ||
                isFilteringWonOrLostDeals ||
                summary[column.id].value === 0
              "
              class="text-neutral-600 dark:text-neutral-300"
              v-text="formatMoney(summary[column.id].value)"
            />

            <span
              v-else
              v-i-tooltip.right="
                $t('deals::deal.stage.weighted_value', {
                  weighted_total: formatMoney(
                    summary[column.id].weighted_value
                  ),
                  win_probability: column.win_probability + '%',
                  total: formatMoney(summary[column.id].value),
                })
              "
              class="inline-flex items-center text-neutral-600 dark:text-neutral-300"
            >
              <Icon icon="Scale" class="mr-1 size-4" />

              <span v-text="formatMoney(summary[column.id].weighted_value)" />
            </span>

            <span class="mx-1 text-neutral-900 dark:text-neutral-300">-</span>

            <span
              class="text-neutral-700 dark:text-neutral-300"
              v-text="
                $t('deals::deal.count.all', { count: summary[column.id].count })
              "
            />
          </div>
        </template>

        <template #actions>
          <IButton
            icon="PlusSolid"
            basic
            small
            @click="createDealViaStage(column)"
          />
        </template>

        <template #card="{ card }">
          <DealsBoardCard
            v-model:amount="card.amount"
            v-model:products-count="card.products_count"
            v-model:swatch-color="card.swatch_color"
            :deal-id="card.id"
            :display-name="card.display_name"
            :path="card.path"
            :status="card.status"
            :incomplete-activities-count="
              card.incomplete_activities_for_user_count
            "
            :tags="card.tags"
            :next-activity-date="card.next_activity_date || undefined"
            :expected-close-date="card.expected_close_date || undefined"
            :falls-behind-expected-close-date="
              card.falls_behind_expected_close_date
            "
            :updated-at="card.updated_at"
            :created-at="card.created_at"
            @update:swatch-color="updateWithoutUpdatingSummary(column)"
            @update:products-count="updateSummary(card.stage_id)"
            @update:amount="updateSummary(card.stage_id)"
            @create-activity-requested="createActivityForDeal = card"
          />
        </template>
      </BaseBoardColumn>
    </BaseBoard>

    <CreateDealModal
      v-model:visible="dealIsBeingCreated"
      :with-extended-submit-buttons="true"
      :go-to-list="false"
      v-bind="dealCreateProps"
      @hidden="dealCreateModalHidden"
      @created="dealCreated"
    />

    <CreateActivityModal
      :visible="createActivityForDeal !== null"
      :deals="[createActivityForDeal]"
      :with-extended-submit-buttons="true"
      :go-to-list="false"
      @created="
        ({ isRegularAction }) => (
          isRegularAction ? (createActivityForDeal = null) : '', fetch()
        )
      "
      @hidden="createActivityForDeal = null"
    />

    <DealsBoardBottomDropper
      v-show="showBottomDropper"
      :resource-id="resourceName"
      @update-request="updateRequest"
      @refresh-requested="fetch"
      @deleted="updateSummary()"
      @won="updateSummary()"
    />
  </MainLayout>
</template>

<script setup>
import {
  computed,
  nextTick,
  onUnmounted,
  ref,
  shallowRef,
  triggerRef,
  watch,
} from 'vue'
import { useI18n } from 'vue-i18n'
import { useTimeoutFn } from '@vueuse/core'
import omit from 'lodash/omit'
import reduce from 'lodash/reduce'

import BaseBoard from '@/Core/components/Board/BaseBoard.vue'
import BaseBoardColumn from '@/Core/components/Board/BaseBoardColumn.vue'
import DataViewFilters from '@/Core/components/DataViews/DataViewFilters.vue'
import DataViews from '@/Core/components/DataViews/DataViews.vue'
import { useAccounting } from '@/Core/composables/useAccounting'
import { useDataViews } from '@/Core/composables/useDataViews'
import { useGlobalEventListener } from '@/Core/composables/useGlobalEventListener'
import { useLoader } from '@/Core/composables/useLoader'
import { useQueryBuilder } from '@/Core/composables/useQueryBuilder'

import DealsBoardBottomDropper from '../components/DealsBoardBottomDropper.vue'
import DealsBoardCard from '../components/DealsBoardCard.vue'
import DealsBoardSortOptions from '../components/DealsBoardSortOptions.vue'
import DealsNavbarViewSelector from '../components/DealsNavbarViewSelector.vue'
import DealsPipelinesDropdown from '../components/DealsPipelinesDropdown.vue'
import { usePipelines } from '../composables/usePipelines'

const defaulSort = {
  field: 'board_order',
  direction: 'asc',
}

const { t } = useI18n()
const { setLoading, isLoading } = useLoader()

const { formatMoney } = useAccounting()

const resourceName = 'deals'
const viewsIdentifier = resourceName
const summary = ref({})

const query = ref({
  q: '',
})

const createActivityForDeal = ref(null)
const createDealStage = ref(null)
const dealIsBeingCreated = ref(false)
const updateInProgress = ref(false)
// use Map to keep the keys in order
const stages = shallowRef(new Map())
const recentlyCreatedDealsCount = ref(0)
const sortBy = ref(defaulSort)
const showBottomDropper = ref(false)
const componentReady = ref(false)
const activePipelineId = ref(null)

const { findPipelineById } = usePipelines()

const { availableRules, queryBuilderRules, hasRules, rulesAreValid, findRule } =
  useQueryBuilder(viewsIdentifier)

const { views } = useDataViews(viewsIdentifier)

const activePipeline = computed(
  () => findPipelineById(activePipelineId.value) || {}
)

watch(activePipeline, (newPipeline, oldPipeline) => {
  // When the pipeline is initially set, we do not fetch as new
  // This will be done via the "DataViewFilters" component "apply" event
  if (Object.keys(oldPipeline).length) {
    fetchAsNew()
  }
})

const isFilteringLostDeals = computed(() => {
  return findRule('status')?.query?.value === 'lost'
})

const isFilteringWonDeals = computed(() => {
  return findRule('status')?.query?.value === 'won'
})

const isFilteringWonOrLostDeals = computed(() => {
  return isFilteringWonDeals.value || isFilteringLostDeals.value
})

const urlPath = computed(() => '/deals/board/' + activePipeline.value.id)

const dealCreateProps = computed(() => {
  let object = {}
  let hiddenFields = []

  object['pipeline'] = activePipeline.value
  hiddenFields.push('pipeline_id')

  if (createDealStage.value) {
    object['stage-id'] = createDealStage.value
    hiddenFields.push('stage_id')
  }

  object['hidden-fields'] = hiddenFields

  return object
})

function getRequestQueryStringParams() {
  return {
    order: sortBy.value,
    filters: rulesAreValid.value ? queryBuilderRules.value : [],
    ...query.value,
  }
}

function onMoveCallback(evt) {
  if (!evt.draggedContext.element.authorizations.update) {
    return false
  }
}

async function handleSortApplied(sort) {
  // user applies sort,
  // makes http request with the new sort to retrieve sorted data from the back-end
  // after deals added to the front end, save each state new board_order
  sortBy.value = sort

  await fetch()

  sortBy.value = defaulSort
  stages.value.forEach(updateWithoutUpdatingSummary)
  Innoclapps.info(t('deals::board.columns_sorted'))
}

async function retrieveViewsAndRules() {
  // We will make sure to retrieve the rules and views here via the table endpoint.
  // Retrieving them via the table endpoint will ensure any views are stored in the database.
  let settingsResponse = await Innoclapps.request(
    `/${resourceName}/table/settings`
  )

  availableRules.value = settingsResponse.data.rules
  views.value = settingsResponse.data.views

  setLoading(false)

  componentReady.value = true
}

/**
 * When deal create modal is hidden
 *
 * Set the create data to false is reset createDealStage
 * The createDealStage data must be resetted because if user
 * click on the top button CREATE, the stage will be selected
 */

function dealCreateModalHidden() {
  // If there are deals created, perform fetch
  // This helps not performing fetch each time the modal is hidden
  // e.q. user can click Create and add another too so in this case,
  // we will increment the recentlyCreatedDealsCount data in the created event
  // and will refetch the board only when the modal is hidden
  if (recentlyCreatedDealsCount.value > 0) {
    fetch()
  }

  createDealStage.value = null
  recentlyCreatedDealsCount.value = 0
}

function createDealViaStage(stage) {
  createDealStage.value = parseInt(stage.id)
  dealIsBeingCreated.value = true
}

/**
 * On deal create reqeuested set the create data to true, so the modal can be shown
 */
function createDealRequested() {
  dealIsBeingCreated.value = true
}

/**
 * On deal create, refetch data and hide the modal
 */
function dealCreated(data) {
  recentlyCreatedDealsCount.value++

  if (data.isRegularAction) {
    dealIsBeingCreated.value = false
  }
}

function handleColumnCardsUpdated() {
  triggerRef(stages)
}

async function loader(columnId, $state) {
  let column = stages.value.get(columnId)

  if (!Object.hasOwn(column, 'infinityState')) {
    column.infinityState = $state
  }

  column.page += 1

  const { data: stage } = await Innoclapps.request(
    `${urlPath.value}/${column.id}`,
    {
      params: {
        ...getRequestQueryStringParams(),
        page: column.page,
      },
    }
  )

  if (stage.cards.length === 0) {
    $state.complete()
  } else {
    stages.value.set(
      stage.id,
      Object.assign(
        stages.value.get(stage.id),
        omit(stage, ['cards', 'summary'])
      )
    )

    stage.cards.forEach(card => stages.value.get(stage.id).cards.push(card))

    $state.loaded()
  }
  triggerRef(stages)
}

function fetchAsNew() {
  fetch(false)
}

async function fetch(refresh = true) {
  let originalInfinityState = {}

  if (refresh) {
    let pages = {}

    stages.value.forEach(stage => {
      pages[stage.id] = stage.page
    })

    query.value.pages = pages
  } else {
    delete query.value.pages

    // We will check before full refresh if the stage has infinity state
    // and we will paused it, then later attach it again to the stage.
    // we are pausing the infinity loader because the scroll is invoked
    // when pre refresh the column had scroll and now the new data of the column does not have scroll
    // e.q. open board with open deals filter, load more stages, change filter to won deals, the infinity
    // will be invoked because the scrollbar changes, but we don't want that as this a full refresh
    stages.value.forEach(stage => {
      if (stage.infinityState) {
        stage.infinityState.pause()
        originalInfinityState[stage.id] = stage.infinityState
      }
    })
  }

  setLoading(true)

  try {
    const { data } = await Innoclapps.request(urlPath.value, {
      params: getRequestQueryStringParams(),
    })

    if (!refresh) {
      stages.value = new Map()
      summary.value = {}
    }

    data.forEach(stage => {
      stage.page = refresh ? stages.value.get(stage.id)?.page || 1 : 1
      stages.value.set(stage.id, omit(stage, ['summary']))

      // Add the infinity state again to the stage and resume it.
      nextTick(() => {
        useTimeoutFn(() => {
          if (Object.hasOwn(originalInfinityState, stage.id)) {
            stages.value.set(
              stage.id,
              Object.assign(stages.value.get(stage.id), {
                infinityState: originalInfinityState[stage.id],
              })
            )

            stages.value.get(stage.id).infinityState.resume()
            delete originalInfinityState[stage.id]
          }
        }, 300)
      })
      summary.value[stage.id] = stage.summary
    })

    if (refresh) {
      triggerRef(stages)
    }

    return data
  } finally {
    setLoading(false)
    delete query.value.pages
  }
}

function updateSummary(stageId = null) {
  Innoclapps.request(
    `${urlPath.value}/summary${stageId ? '/' + stageId : ''}`,
    {
      params: getRequestQueryStringParams(),
    }
  ).then(({ data }) =>
    Object.keys(data).forEach(
      stageId => (summary.value[stageId] = data[stageId])
    )
  )
}

function updateWithoutUpdatingSummary(stage) {
  updateStage(stage, false)
}

/**
 * Update column deals order and stage belongings
 */
function updateStage(stage, summaryRequest = true) {
  updateRequest(
    reduce(
      stage.cards,
      (result, deal, key) => {
        result.push({
          id: deal.id,
          board_order: key + 1,
          stage_id: stage.id,
          swatch_color: deal.swatch_color ? deal.swatch_color : null,
        })

        return result
      },
      []
    ),
    summaryRequest
  )
}

/**
 * Perform an update request
 */
function updateRequest(data, summaryRequest = true) {
  updateInProgress.value = true

  Innoclapps.request()
    .post(urlPath.value, data)
    .finally(() => {
      triggerRef(stages)
      updateInProgress.value = false

      if (summaryRequest) {
        updateSummary()
      }
    })
}

/**
 * Update the deals order and stage
 */
function handleColumnUpdatedEvent(event) {
  updateStage(stages.value.get(event.columnId))
}

/**
 * Check whether there is update in progress and show message before leaving
 */
function checkUpdateInProgress() {
  if (updateInProgress.value) {
    window.confirm(
      'Update is in progress, please wait till the update finishes, if you still can see the message after few seconds, try to force-refresh the page.'
    )
  }
}

retrieveViewsAndRules()

window.addEventListener('beforeunload', checkUpdateInProgress)
useGlobalEventListener('floating-resource-updated', () => fetch())

onUnmounted(() => {
  window.removeEventListener('beforeunload', checkUpdateInProgress)
})
</script>
