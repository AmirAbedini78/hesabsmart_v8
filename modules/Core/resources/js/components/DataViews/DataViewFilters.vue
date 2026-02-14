<template>
  <div
    class="-mx-2.5 snap-x items-center overflow-x-auto scrollbar-thin scrollbar-track-neutral-200 scrollbar-thumb-neutral-300 dark:scrollbar-thumb-neutral-600 sm:-mx-0 sm:flex"
  >
    <DataViewQuickFilters
      class="shrink-0"
      :identifier="identifier"
      @changed="applyAndSaveRules"
    />
  </div>

  <div class="-mx-2.5 shrink-0 sm:-mx-0">
    <IButton
      icon="AdjustmentsVerticalSolid"
      :class="hasRulesApplied && 'relative rounded-r-none focus:z-10'"
      :active="hasRulesApplied"
      :text="$t('core::filters.advanced_filters')"
      basic
      @click="filtersBuilderVisible = true"
    />

    <IButton
      v-show="hasRulesApplied"
      v-i-tooltip="$t('core::filters.clear_all')"
      icon="XSolid"
      class="rounded-l-none"
      :active="true"
      basic
      @click="resetRulesAndRefresh"
    />
  </div>

  <IModal v-model:visible="filtersBuilderVisible" size="xl">
    <template #title>
      {{ $t('core::filters.all') }}

      <IText
        v-if="isSystemDefault || !authorizedToUpdate"
        class="max-w-[95%] font-medium text-info-800 dark:text-info-400"
      >
        {{
          isSystemDefault
            ? $t('core::data_views.filter_is_readonly')
            : $t('core::data_views.filter_cannot_be_saved')
        }}
      </IText>
    </template>

    <IModalSeparator />

    <QueryBuilder v-bind="$attrs" :identifier="identifier" />

    <IAlert
      v-if="hasRulesAppliedWithAuthorizationAndIsShared && !isSystemDefault"
      class="mb-3"
    >
      <IAlertBody>
        {{
          $t(
            'core::data_views.save_shared_with_authorizeable_filters_disabled',
            {
              rules: rulesLabelsWithAuthorization.join(', '),
            }
          )
        }}
      </IAlertBody>
    </IAlert>

    <template #modal-footer="{ cancel }">
      <div class="flex items-center justify-between">
        <div>
          <IButton
            v-if="hasRulesApplied"
            class="font-medium"
            :text="$t('core::filters.clear_all')"
            basic
            @click="resetRulesAndRefresh"
          />
        </div>

        <div class="flex items-center gap-x-2">
          <IButton basic @click="cancel">{{ $t('core::app.hide') }}</IButton>

          <IButton
            variant="primary"
            :disabled="saving || !rulesAreValid"
            @click="applyAndSaveRules"
          >
            {{
              canSave
                ? $t('core::filters.save_and_apply')
                : $t('core::filters.apply')
            }}
          </IButton>
        </div>
      </div>
    </template>
  </IModal>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { onBeforeUnmount } from 'vue'
import cloneDeep from 'lodash/cloneDeep'

import QueryBuilder from '@/Core/components/QueryBuilder'
import { useQueryBuilder } from '@/Core/composables/useQueryBuilder'

import { useDataViews } from '../../composables/useDataViews'

import DataViewQuickFilters from './DataViewQuickFilters.vue'

defineOptions({ inheritAttrs: false })

const props = defineProps({
  identifier: { required: true, type: String },
})

const emit = defineEmits(['apply'])

const saving = ref(false)

const {
  queryBuilderRules,
  rulesAreValid,
  hasRulesApplied,
  resetQueryBuilderRules,
  hasRulesAppliedWithAuthorization,
  rulesLabelsWithAuthorization,
} = useQueryBuilder(props.identifier)

const {
  filtersBuilderVisible,
  activeView,
  patchView,
  findView,
  hasActiveView,
} = useDataViews(props.identifier)

const authorizedToUpdate = computed(
  () => hasActiveView.value && activeView.value.authorizations.update
)

const isSystemDefault = computed(
  () => hasActiveView.value && activeView.value.is_system_default
)

const hasRulesAppliedWithAuthorizationAndIsShared = computed(
  () =>
    hasRulesAppliedWithAuthorization.value &&
    hasActiveView.value &&
    activeView.value.is_shared
)

const canSave = computed(() => {
  if (isSystemDefault.value) {
    return false
  }

  if (hasRulesAppliedWithAuthorizationAndIsShared.value) {
    return false
  }

  return authorizedToUpdate.value
})

function setRulesFromActiveView() {
  queryBuilderRules.value = cloneDeep(activeView.value.rules)

  // Add the in query builder flag indicator.
  patchView({ ...activeView.value, _in_query_builder: true })
}

watch(
  () => activeView.value?.id,
  (newVal, oldVal) => {
    // Remove the in query builder flag when view changes
    if (oldVal) {
      patchView({ ...findView(oldVal), _in_query_builder: false })
    }

    // We update the query builder rules only if the view changes to avoid resetting
    // user-modified rules when navigating back. If the view is being activated for
    // the first time and has rules applied, we don't overwrite them. However, if the
    // new view is different from the current one, we replace the query builder rules.
    if (newVal) {
      if (
        oldVal ||
        (!oldVal && !hasRulesApplied.value) ||
        (!oldVal &&
          hasRulesApplied.value &&
          !findView(newVal)._in_query_builder)
      ) {
        setRulesFromActiveView()
      }

      apply()
    }
  },
  { immediate: true }
)

/**
 * Save the view rules.
 */
async function saveRules() {
  const { data: view } = await Innoclapps.request().put(
    `/views/${activeView.value.id}`,
    {
      rules: queryBuilderRules.value,
    }
  )

  patchView({
    ...activeView.value,
    rules: view.rules,
  })
}

/**
 * Save the view rules.
 */
async function applyAndSaveRules() {
  saving.value = true

  try {
    if (canSave.value) {
      await saveRules()
    }

    filtersBuilderVisible.value = false

    apply()
  } finally {
    saving.value = false
  }
}

/**
 * Emit "apply" filters event.
 */
function apply() {
  emit('apply', queryBuilderRules.value)
}

/**
 * Reset the applied query builder rules and perform refresh.
 */
async function resetRulesAndRefresh() {
  resetQueryBuilderRules()

  if (canSave.value) {
    await saveRules()
  }

  apply()
}

onBeforeUnmount(() => {
  filtersBuilderVisible.value = false

  if (!rulesAreValid.value) {
    resetQueryBuilderRules()
  }
})
</script>
