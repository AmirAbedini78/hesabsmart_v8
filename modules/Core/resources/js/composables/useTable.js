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
import { computed, toValue } from 'vue'
import { useStore } from 'vuex'
import cloneDeep from 'lodash/cloneDeep'
import get from 'lodash/get'
import isNil from 'lodash/isNil'
import sortBy from 'lodash/sortBy'

import { useDataViews } from './useDataViews'
import { emitGlobal } from './useGlobalEventListener'

/**
 * Composable for table operations.
 *
 * @param {string} identifier - The identifier of the table.
 */
export function useTable(identifier, cacheKey = null) {
  /** @type {import('vuex').Store} */
  const store = useStore()

  const { activeView } = useDataViews(identifier)

  /** @type {import('vue').ComputedRef<Object>} */
  let _cacheKey = computed(() => toValue(cacheKey) || toValue(identifier))

  /** @type {import('vue').ComputedRef<Object>} */
  const settings = computed({
    set(newValue) {
      store.commit('table/SET_SETTINGS', {
        id: _cacheKey.value,
        settings: newValue,
      })
    },
    get() {
      return store.state.table.settings[_cacheKey.value] || {}
    },
  })

  /** @type {import('vue').ComputedRef<Object>} */
  const columns = computed(() => {
    return activeView.value
      ? sortBy(
          mergeViewColumns(
            get(settings.value, 'columns', []),
            get(activeView.value, 'config.table.columns', [])
          ),
          'order'
        )
      : sortBy(settings.value.columns || [], 'order')
  })

  /** @type {import('vue').ComputedRef<Object>} */
  const perPage = computed(() =>
    parseInt(
      get(
        activeView.value,
        'config.table.perPage',
        get(settings.value, 'defaults.perPage', 25)
      )
    )
  )

  /** @type {import('vue').ComputedRef<Object>} */
  const isCondensed = computed(() =>
    get(
      activeView.value,
      'config.table.condensed',
      get(settings.value, 'defaults.condensed', false)
    )
  )

  /** @type {import('vue').ComputedRef<Object>} */
  const isBordered = computed(() =>
    get(
      activeView.value,
      'config.table.bordered',
      get(settings.value, 'defaults.bordered', false)
    )
  )

  /** @type {import('vue').ComputedRef<Object>} */
  const pollingInterval = computed(() =>
    get(
      activeView.value,
      'config.table.pollingInterval',
      get(settings.value, 'defaults.pollingInterval')
    )
  )

  /** @type {import('vue').ComputedRef<Object>} */
  const defaultOrder = computed(() =>
    get(
      activeView.value,
      'config.table.order',
      get(settings.value, 'defaults.order', [])
    )
  )

  /** @type {import('vue').ComputedRef<Object>} */
  const maxHeight = computed(() =>
    get(
      activeView.value,
      'config.table.maxHeight',
      get(settings.value, 'defaults.maxHeight')
    )
  )

  /** @type {import('vue').ComputedRef<Object>} */
  const maxHeightPx = computed(() => {
    let height = maxHeight.value

    /**
     * When no maxHeight is provided, just set the maxHeight to big number e.q. 10000px because when the user
     * previous had height, and updated resetted the table, VueJS won't set the height to auto or remove the previous height
     */
    return !isNil(height) && height > 0 ? height + 'px' : '10000px'
  })

  /** @type {import('vue').ComputedRef<Object>} */
  const isSticky = computed(() => !isNil(maxHeight.value))

  /**
   * Reloads the table.
   */
  function reloadTable() {
    emitGlobal('reload-resource-table', toValue(identifier))
  }

  /**
   * Fetch the table settings.
   *
   * @param {string} resourceName
   * @param {Object} config
   * @returns Promise
   */
  async function fetchSettings(resourceName, config) {
    let settingsRetrieved = Object.keys(settings.value).length > 0

    if (settingsRetrieved) {
      return settings.value
    }

    let { data } = await Innoclapps.request(
      `/${resourceName}/table/settings`,
      config
    )

    return data
  }

  /**
   * Fetch the table actions and set them in the store.
   *
   * @param {string} resourceName
   * @param {Object} config
   * @returns Promise
   */
  async function fetchActions(resourceName, config) {
    let { data } = await Innoclapps.request(
      `/${resourceName}/table/settings`,
      config
    )

    settings.value = { ...settings.value, ...{ actions: data.actions } }
  }

  function mergeViewColumns(availableColumns, viewColumnsDefinition) {
    return cloneDeep(availableColumns).map(col => {
      let customizedColumn = viewColumnsDefinition.find(
        column => column.attribute === col.attribute
      )

      if (customizedColumn) {
        return {
          ...col,
          ...customizedColumn,
        }
      }

      return col
    })
  }

  return {
    reloadTable,
    settings,
    fetchSettings,
    fetchActions,
    columns,
    perPage,
    isCondensed,
    isBordered,
    pollingInterval,
    defaultOrder,
    maxHeight,
    maxHeightPx,
    isSticky,
  }
}

/**
 * Flush all the tables settings cache.
 */
export function useFlushTableSettings() {
  /** @type {import('vuex').Store} */
  const store = useStore()

  return () => store.commit('table/FLUSH_SETTINGS')
}
