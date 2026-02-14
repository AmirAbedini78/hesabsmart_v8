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
import { shallowReactive } from 'vue'
import isNil from 'lodash/isNil'

import {
  createGroup,
  createRule,
  getDefaultQuery,
} from '../components/QueryBuilder/utils'

const state = {
  // The available rules for the query builder by identifier.
  availableRules: shallowReactive({}),

  // The rules in the query builder by identifier.
  queryBuilderRules: {},
}

const mutations = {
  /**
   * Set the builder available rules for identifier.
   */
  SET_AVAILABLE_RULES(state, { identifier, rules }) {
    state.availableRules[identifier] = rules
  },

  /**
   * Set the query builder rules for identifier.
   */
  SET_BUILDER_RULES(state, { identifier, rules }) {
    state.queryBuilderRules[identifier] = rules
  },

  /**
   * Reset the query builder rules for identifier.
   */
  RESET_BUILDER_RULES(state, { identifier }) {
    state.queryBuilderRules[identifier] = getDefaultQuery()
  },

  /**
   * Add new query builder group.
   */
  ADD_GROUP(state, { identifier, group }) {
    state.queryBuilderRules[identifier] =
      state.queryBuilderRules[identifier] || []

    state.queryBuilderRules[identifier].push(group)
  },

  /**
   * Remove group for the query builder rules.
   */
  REMOVE_GROUP(state, { identifier, index, childIndex }) {
    if (isNil(childIndex)) {
      state.queryBuilderRules[identifier].splice(index, 1)
    } else {
      state.queryBuilderRules[identifier][index].children.splice(childIndex, 1)
    }
  },

  /**
   * Remove query builder child group rule.
   */
  REMOVE_GROUP_RULE(
    state,
    { ruleId, identifier, groupIndex, childGroupIndex }
  ) {
    let children = state.queryBuilderRules[identifier][groupIndex].children

    if (!isNil(childGroupIndex)) {
      children = children[childGroupIndex].query.children
    }

    let currentRuleIndex = children.findIndex(r => r.query.rule == ruleId)

    if (currentRuleIndex !== -1) {
      children.splice(currentRuleIndex, 1)
    }
  },

  /**
   * Add query builder child group rule.
   */
  ADD_GROUP_RULE(state, { rule, identifier, groupIndex, childGroupIndex }) {
    let children = state.queryBuilderRules[identifier][groupIndex].children

    if (!isNil(childGroupIndex)) {
      children = children[childGroupIndex].query.children
    }

    children.push(rule)
  },

  /**
   * Update query builder rule.
   */
  UPDATE_GROUP_RULE(
    state,
    { ruleId, identifier, rule, groupIndex, childGroupIndex }
  ) {
    let children = state.queryBuilderRules[identifier][groupIndex].children

    if (!isNil(childGroupIndex)) {
      children = children[childGroupIndex].query.children
    }

    let currentRuleIndex = children.findIndex(r => r.query.rule == ruleId)

    if (currentRuleIndex !== -1) {
      children[currentRuleIndex].query = Object.assign(
        children[currentRuleIndex].query,
        rule
      )
    }
  },

  /**
   * Add group to the given query.
   */
  ADD_QUERY_GROUP(state, query) {
    query.children.push({
      type: 'group',
      query: createGroup(),
    })
  },

  /**
   * Set the child of the given query.
   */
  SET_QUERY_CHILDREN(state, { query, children }) {
    query.children = children
  },

  /**
   * Add new rule to the given query.
   */
  ADD_QUERY_RULE(state, query) {
    query.children.push(createRule(null, null, null, null))
  },

  /**
   * Remove child from the given query.
   */
  REMOVE_QUERY_CHILD(state, { query, index }) {
    query.children.splice(index, 1)
  },

  /**
   * Update the given query rule.
   */
  UPDATE_QUERY_RULE(state, { query, value }) {
    query.rule = value
  },

  /**
   * Update given query value.
   */
  UPDATE_QUERY_VALUE(state, { query, value }) {
    query.value = value
  },

  /**
   * Update given query type.
   */
  UPDATE_QUERY_TYPE(state, { query, value }) {
    query.type = value
  },

  /**
   * Update given query condition.
   */
  UPDATE_QUERY_CONDITION(state, { query, value }) {
    query.condition = value
  },

  /**
   * Update given query operand.
   */
  UPDATE_QUERY_OPERAND(state, { query, value }) {
    query.operand = value
  },

  /**
   * Update given query operator.
   */
  UPDATE_QUERY_OPERATOR(state, { query, value }) {
    query.operator = value
  },
}

export default {
  namespaced: true,
  state,
  mutations,
}
