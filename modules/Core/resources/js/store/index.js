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
import { createStore } from 'vuex'
import findIndex from 'lodash/findIndex'

export default createStore({
  state: {
    sidebarMenuItems: [],
    sidebarOpen: false,
  },
  mutations: {
    /**
     * Toggle the sidebar visibility
     */
    SET_SIDEBAR_OPEN(state, value) {
      state.sidebarOpen = value
    },

    /**
     * Set available sidebar menu items.
     */
    SET_SIDEBAR_MENU_ITEMS(state, items) {
      state.sidebarMenuItems = items
    },

    /**
     * Update sidebar menu item.
     */
    UPDATE_SIDEBAR_MENU_ITEM(state, data) {
      const index = findIndex(state.sidebarMenuItems, ['id', data.id])

      state.sidebarMenuItems[index] = Object.assign(
        {},
        state.sidebarMenuItems[index],
        data.data
      )
    },
  },
  getters: {
    /**
     * Get a sidebar menu item by given id.
     */
    getSidebarMenuItem: state => id => {
      return state.sidebarMenuItems[
        findIndex(state.sidebarMenuItems, ['id', id])
      ]
    },
  },
  modules: {},
  strict: process.env.NODE_ENV !== 'production',
})
