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
import isEmpty from 'lodash/isEmpty'
import isNaN from 'lodash/isNaN'
import isNumber from 'lodash/isNumber'

function isBlank(value) {
  return (
    (isEmpty(value) && typeof value !== 'boolean' && !isNumber(value)) ||
    isNaN(value)
  )
}

export default isBlank
