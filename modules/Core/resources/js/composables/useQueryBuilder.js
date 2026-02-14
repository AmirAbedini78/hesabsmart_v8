/**
 * Concord CRM - https://www.concordcrm.com
 *
 * @version   1.7.0
 *
 * @link      Releases - https://www.concordcrm.com/releases
 * @link      Terms Of Service - https://www.concordcrm.com/terms
 *
 * @copyright Copyright (c) 2022-2025 KONKORD DIGITAL
 */
import { computed, toValue, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { useStore } from 'vuex'
import isNil from 'lodash/isNil'

import {
  createGroup,
  createRule,
  getValuesForValidation,
} from '../components/QueryBuilder/utils'
import { isBlank } from '../utils'

export function useQueryBuilder(identifier) {
  const store = useStore()

  function callCommit(commit, params) {
    store.commit(`queryBuilder/${commit}`, {
      identifier: toValue(identifier),
      ...params,
    })
  }

  /**+
   * Indicates if there is a quick filters group in the query builder.
   *
   * @type {import('vue').ComputedRef<boolean>}
   */
  const hasQuickFiltersGroup = computed(() => {
    return queryBuilderRules.value.some(group => group.quick)
  })

  /**
   * The available rules/filters for the identifier.
   *
   * @type {import('vue').ComputedRef<boolean>}
   */
  const availableRules = computed({
    set(newValue) {
      callCommit('SET_AVAILABLE_RULES', { rules: newValue })
    },
    get() {
      return store.state.queryBuilder.availableRules[toValue(identifier)] || []
    },
  })

  /**
   * Get the current rules in the query builder.
   *
   * @type {import('vue').ComputedRef<boolean>}
   */
  const queryBuilderRules = computed({
    set(newValue) {
      callCommit('SET_BUILDER_RULES', { rules: newValue })
    },
    get() {
      return (
        store.state.queryBuilder.queryBuilderRules[toValue(identifier)] || []
      )
    },
  })

  /**
   * Indicates if there are any rules in the query builder.
   *
   * @type {import('vue').ComputedRef<boolean>}
   */
  const hasAnyBuilderRules = computed(() =>
    (queryBuilderRules.value || []).some(
      group => group.children && group.children.length > 0
    )
  )

  /**
   * Get the applied query builder rules values.
   *
   * @type {import('vue').ComputedRef<boolean>}
   */
  const rulesValidationValues = computed(() =>
    getValuesForValidation(queryBuilderRules.value, availableRules.value)
  )

  /**
   * Total number of rules in the query builder.
   *
   * Checks are performed based on the values that exists.
   *
   * @type {import('vue').ComputedRef<boolean>}
   */
  const totalValidRules = computed(() => rulesValidationValues.value.length)

  /**
   * Indicates if there are rules applied in the query builder.
   *
   * @type {import('vue').ComputedRef<boolean>}
   */
  const hasRulesApplied = computed(() => {
    // If there are values, this means that there is at least one rule added in the filter
    return totalValidRules.value > 0
  })

  /**
   * Indicates if the applied rules in the query builder are valid.
   *
   * @type {import('vue').ComputedRef<boolean>}
   */
  const rulesAreValid = computed(() => {
    if (!hasRulesApplied.value) {
      return true
    }

    const totalValid = rulesValidationValues.value.filter(
      value => !isBlank(value)
    ).length

    // If all rules has values, the filters are valid
    return totalValidRules.value === totalValid
  })

  /**
   * Indicates whether the identifier has available rules/filters.
   *
   * @type {import('vue').ComputedRef<boolean>}
   */
  const hasRules = computed(() =>
    !availableRules.value ? false : availableRules.value.length > 0
  )

  /**
   * Indicates if there are disabled rules in the query builder.
   *
   * @type {import('vue').ComputedRef<boolean>}
   */
  const hasDisabledRules = computed(() =>
    Boolean(findRule(rule => rule.disabled === true))
  )

  /**
   * Get all the rules in the query builder that are having authorization.
   *
   * @type {import('vue').ComputedRef<boolean>}
   */
  const rulesWithAuthorization = computed(() =>
    availableRules.value.filter(rule => rule.hasAuthorization)
  )

  /**
   * Indicates if there are rules in the query builder that are with authorization.
   *
   * @type {import('vue').ComputedRef<boolean>}
   */
  const hasRulesAppliedWithAuthorization = computed(() =>
    rulesWithAuthorization.value.some(rule => findRule(rule.id))
  )

  /**
   * Get the rule labels with authorization.
   *
   * @type {import('vue').ComputedRef<boolean>}
   */
  const rulesLabelsWithAuthorization = computed(() => {
    return rulesWithAuthorization.value
      .filter(r => Boolean(findRule(r.id)))
      .map(r => r.label)
  })

  /**
   * Add new main query builder group.
   */
  function addGroup(group) {
    // This will be the index of the group after it's added.
    let index = queryBuilderRules.value.length

    callCommit('ADD_GROUP', { group })

    return index
  }

  /**
   * Update the given rule id value.
   */
  function updateRuleValue(
    ruleId,
    newValue,
    groupIndex,
    childGroupIndex = null
  ) {
    callCommit('UPDATE_GROUP_RULE', {
      ruleId,
      groupIndex,
      childGroupIndex,
      rule: { value: newValue },
    })
  }

  /**
   * Remove group from the query builder.
   */
  function removeGroup(index, childIndex) {
    callCommit('REMOVE_GROUP', {
      index: index,
      childIndex: childIndex,
    })
  }

  /**
   * Remove a rule from the query builder group.
   */
  function removeGroupRule(ruleId, groupIndex = 0, childGroupIndex = null) {
    callCommit('REMOVE_GROUP_RULE', {
      ruleId,
      groupIndex,
      childGroupIndex,
    })
  }

  /**
   * Find rule from the query builder from the given rule attribute ID.
   *
   * This function will return the first rule that is found, a duplicate
   * rule may exists in the same or another group.
   */
  function findRule(ruleIdOrCallback) {
    let result = undefined

    queryBuilderRules.value.every(group => {
      result = findRuleNested(group.children || [], ruleIdOrCallback)

      return result ? false : true
    })

    return result
  }

  /**
   * Add rule to a group.
   */
  function addGroupRule(rule, groupIndex, childGroupIndex = null) {
    callCommit('ADD_GROUP_RULE', {
      rule,
      groupIndex,
      childGroupIndex,
    })
  }

  /**
   * Find a rule from the given group.
   */
  function findGroupRule(ruleId, groupIndex = 0, childGroupIndex = null) {
    if (!queryBuilderRules.value[groupIndex]) {
      return null
    }

    let children = queryBuilderRules.value[groupIndex].children

    if (!isNil(childGroupIndex)) {
      children = children[childGroupIndex]?.query.children || []
    }

    return children.find(r => r.query.rule == ruleId)
  }

  /**
   * Reset the query builder rules.
   */
  function resetQueryBuilderRules() {
    callCommit('RESET_BUILDER_RULES')
  }

  const totalGroups = computed(() => queryBuilderRules.value.length)

  /**
   * Ensure that there is always one advanced group in the builder.
   */
  watch(
    totalGroups,
    newVal => {
      if (newVal === 0) {
        resetQueryBuilderRules()
      } else if (newVal === 1 && hasQuickFiltersGroup.value) {
        // When there is only one group and it's the quick filters group,
        // we will make sure to add new "advanced filters" group on top.
        const currentGroupRules = queryBuilderRules.value[0]
        resetQueryBuilderRules()
        addGroup(currentGroupRules)
      }
    },
    {
      immediate: true,
    }
  )

  return {
    availableRules,
    queryBuilderRules,
    hasAnyBuilderRules,
    rulesWithAuthorization,
    hasRules,
    hasRulesAppliedWithAuthorization,
    rulesLabelsWithAuthorization,
    hasRulesApplied,
    hasDisabledRules,
    rulesAreValid,
    totalValidRules,
    addGroupRule,
    findRule,
    updateRuleValue,
    removeGroupRule,
    removeGroup,
    findGroupRule,
    resetQueryBuilderRules,
    addGroup,
    createRule,
    createGroup,
  }
}

export function useGroupBackgroundClasses(depth) {
  return computed(() => ({
    'bg-white dark:bg-neutral-900': toValue(depth) === 1,
    'bg-neutral-50/60 dark:bg-neutral-500/10': toValue(depth) === 2,
  }))
}

export function useQueryBuilderLabels() {
  const { t } = useI18n()

  const labels = {
    operatorLabels: {
      is: t('core::filters.operators.is'),
      equal: t('core::filters.operators.equal'),
      not_equal: t('core::filters.operators.not_equal'),
      in: t('core::filters.operators.in'),
      not_in: t('core::filters.operators.not_in'),
      less: t('core::filters.operators.less'),
      less_or_equal: t('core::filters.operators.less_or_equal'),
      greater: t('core::filters.operators.greater'),
      greater_or_equal: t('core::filters.operators.greater_or_equal'),
      between: t('core::filters.operators.between'),
      not_between: t('core::filters.operators.not_between'),
      begins_with: t('core::filters.operators.begins_with'),
      not_begins_with: t('core::filters.operators.not_begins_with'),
      contains: t('core::filters.operators.contains'),
      not_contains: t('core::filters.operators.not_contains'),
      ends_with: t('core::filters.operators.ends_with'),
      not_ends_with: t('core::filters.operators.not_ends_with'),
      is_null: t('core::filters.operators.is_null'),
      is_not_null: t('core::filters.operators.is_not_null'),

      // Not used
      is_empty: t('core::filters.operators.is_empty'),
      is_not_empty: t('core::filters.operators.is_not_empty'),
    },
    matchType: t('core::filters.match_type'),
    matchTypeAll: t('core::filters.match_type_all'),
    matchTypeAny: t('core::filters.match_type_any'),
    addCondition: t('core::filters.add_condition'),
    addAnotherCondition: t('core::filters.add_another_condition'),
    addGroup: t('core::filters.add_group'),
    selectRule: t('core::filters.select_rule'),
  }

  return { labels }
}

const findRuleNested = (rules, ruleIdOrCallback) => {
  let result = undefined

  rules.every(rule => {
    if (Object.hasOwn(rule.query, 'children') && rule.query.children) {
      result = findRuleNested(rule.query.children, ruleIdOrCallback)
    } else if (
      typeof ruleIdOrCallback === 'function' &&
      ruleIdOrCallback(rule) === true
    ) {
      result = rule
    } else if (ruleIdOrCallback == rule.query.rule) {
      result = rule
    }

    return result ? false : true
  })

  return result
}
