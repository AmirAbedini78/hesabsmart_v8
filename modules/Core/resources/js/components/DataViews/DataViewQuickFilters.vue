<template>
  <div class="flex flex-wrap items-center gap-x-1 sm:flex-nowrap">
    <DataViewQuickFiltersItem
      v-for="rule in quickFilters"
      :key="rule.id"
      v-model="selectedRules[rule.id]"
      :identifier="identifier"
      :rule="rule"
      @changed="handleRuleChanged"
    />
  </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import cloneDeep from 'lodash/cloneDeep'
import isNil from 'lodash/isNil'

import { useDataViews } from '@/Core/composables/useDataViews'
import { useQueryBuilder } from '@/Core/composables/useQueryBuilder'
import { isBlank } from '@/Core/utils'

import DataViewQuickFiltersItem from './DataViewQuickFiltersItem.vue'

const props = defineProps({
  identifier: { type: String, required: true },
})

const emit = defineEmits(['changed'])

const selectedRules = ref({})

const {
  availableRules,
  queryBuilderRules,
  createGroup,
  addGroupRule,
  findGroupRule,
  updateRuleValue,
  addGroup,
  createRule,
  removeGroup,
  removeGroupRule,
} = useQueryBuilder(props.identifier)

const { activeView } = useDataViews(props.identifier)

const quickFilters = computed(() =>
  availableRules.value.filter(
    rule => rule.quickFilter !== null && rule.quickFilter.options.length > 0
  )
)

const quickFiltersGroupIndex = computed(() =>
  queryBuilderRules.value.findIndex(group => group.quick === true)
)

function setQuickFiltersValues() {
  quickFilters.value.forEach(rule => {
    selectedRules.value[rule.id] =
      rule.quickFilter.multiple === true ? [] : null
  })

  if (
    !isNil(quickFiltersGroupIndex.value) &&
    quickFiltersGroupIndex.value !== -1
  ) {
    queryBuilderRules.value[quickFiltersGroupIndex.value]?.children.forEach(
      rule => {
        selectedRules.value[rule.query.rule] = rule.query.value
      }
    )
  }
}

watch(() => activeView.value?.id, setQuickFiltersValues)

watch(
  quickFiltersGroupIndex,
  () => {
    setQuickFiltersValues()
  },
  { immediate: true, flush: 'post' }
)

function handleRuleChanged(payload) {
  const { rule, value, option } = payload

  if (quickFiltersGroupIndex.value === -1) {
    addGroup(createGroup([], { quick: true }))
  }

  const addCurrentRule = () =>
    addGroupRule(
      createRule(
        rule.type,
        rule.id,
        option.operator || rule.quickFilter.operator,
        cloneDeep(value)
      ),
      quickFiltersGroupIndex.value
    )

  let qbRule = findGroupRule(rule.id, quickFiltersGroupIndex.value)

  if (qbRule) {
    if (isBlank(value)) {
      removeGroupRule(rule.id, quickFiltersGroupIndex.value)
    } else if (rule.quickFilter.operator) {
      // If the rule quick filter has operator, we will remove and re-add the rule
      // so the operator is properly updated, as we are not updating the operator here, only value.
      // e.q. choose owner and then choose unassigned, won't work.
      removeGroupRule(rule.id, quickFiltersGroupIndex.value)
      addCurrentRule()
    } else {
      updateRuleValue(rule.id, cloneDeep(value), quickFiltersGroupIndex.value)
    }
  } else {
    addCurrentRule()
  }

  // When there are no more filters applied in the group, remove the group.
  if (
    queryBuilderRules.value[quickFiltersGroupIndex.value].children.length === 0
  ) {
    removeGroup(quickFiltersGroupIndex.value)
  }

  emit('changed')
}
</script>
