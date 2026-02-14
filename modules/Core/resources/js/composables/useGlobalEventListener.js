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
import { onUnmounted } from 'vue'
import castArray from 'lodash/castArray'

/**
 * @param  {String|Array} events
 * @param  {Function} callback
 */
export function useGlobalEventListener(events, callback) {
  castArray(events).forEach(eventName => {
    Innoclapps.$on(eventName, callback)

    onUnmounted(() => {
      Innoclapps.$off(eventName, callback)
    })
  })
}

/**
 * @param  {String} eventName
 * @param  {Mixed} params
 */
export function emitGlobal(...args) {
  Innoclapps.$emit(...args)
}
