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
import find from 'lodash/find'
import findIndex from 'lodash/findIndex'
import orderBy from 'lodash/orderBy'

const state = {
  // The available saved views for identifier
  views: shallowReactive({}),
  // Visible views builders by identifier
  visibleBuilders: {},
  // Active views by identifier
  activeViews: {},
}

const mutations = {
  /**
   * Set the identifier views in store.
   */
  SET(state, { identifier, views }) {
    state.views[identifier] = views
  },

  /**
   * Update the view in store.
   */
  UPDATE(state, { identifier, view }) {
    let index = findIndex(state.views[identifier], ['id', parseInt(view.id)])

    if (index !== -1) {
      let updatedViews = [...state.views[identifier]]
      updatedViews[index] = view
      state.views[identifier] = updatedViews
    }
  },

  /**
   * Add new view in store.
   */
  PUSH(state, { identifier, view }) {
    let updatedViews = [...state.views[identifier]]
    updatedViews.push(view)
    state.views[identifier] = updatedViews
  },

  /**
   * Remove view from store.
   */
  REMOVE(state, { identifier, id }) {
    let index = findIndex(state.views[identifier], ['id', parseInt(id)])

    if (index !== -1) {
      let updatedViews = [...state.views[identifier]]
      updatedViews.splice(index, 1)
      state.views[identifier] = updatedViews
    }
  },

  /**
   * Set view as active for the identifier.
   */
  SET_ACTIVE(state, { identifier, id }) {
    state.activeViews[identifier] = id
  },

  /**
   * Set filters builder visible indicator for resource.
   */
  SET_BUILDER_VISIBLE(state, { identifier, viewId, visible }) {
    state.visibleBuilders[identifier] = {
      ...(state.visibleBuilders[identifier] || {}),
      [viewId]: visible,
    }
  },
}

const getters = {
  /**
   * Get all identifier views.
   */
  getAll: state => identifier => {
    return orderBy(
      state.views[identifier],
      ['user_order', 'created_at'],
      ['asc', 'asc']
    )
  },

  /**
   * Get identifier view by id.
   */
  getById: state => (identifier, id) => {
    return find(state.views[identifier], ['id', parseInt(id)])
  },

  /**
   * Get active view for the given identifier.
   */
  getActive: state => identifier => {
    const activeId = state.activeViews[identifier]

    if (activeId === undefined) return null

    return find(state.views[identifier], ['id', parseInt(activeId)])
  },

  /**
   * Check whether the filters builder is visible for the given identifier and view.
   */
  filtersBuilderVisible: state => (identifier, viewId) => {
    return Boolean(state.visibleBuilders[identifier]?.[viewId])
  },
}

export default {
  namespaced: true,
  state,
  getters,
  mutations,
}
