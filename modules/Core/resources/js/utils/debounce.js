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
import baseDebounce from 'lodash/debounce'

const DEFAULT_WAIT = 600

function parseWait(value) {
  if (
    value === false ||
    (typeof value !== 'string' &&
      typeof value !== 'boolean' &&
      typeof value !== 'number')
  ) {
    return 0
  }

  if (value === true) {
    return DEFAULT_WAIT
  }

  if (typeof value === 'string') {
    return value ? parseInt(value, 10) : DEFAULT_WAIT
  }

  return value
}

function debounce(callback, wait = 0, options = {}) {
  return baseDebounce(callback, parseWait(wait), options)
}

export default debounce
