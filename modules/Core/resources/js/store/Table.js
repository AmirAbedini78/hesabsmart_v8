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

const state = {
  settings: shallowReactive({}),
}

const mutations = {
  /**
   * Set the table settings in store.
   *
   * @param {Object} state
   * @param {Object} data
   */
  SET_SETTINGS(state, data) {
    state.settings[data.id] = data.settings
  },

  /**
   * Flush all tables settings.
   */
  FLUSH_SETTINGS(state) {
    for (let i in state.settings) {
      state.settings[i] = {}
    }
  },
}

export default {
  namespaced: true,
  state,
  mutations,
}
