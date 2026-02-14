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
import hexRgb from 'hex-rgb'

/**
 * Convert the given hex color to Tailwind compatible color.
 * @param {string} hex
 * @returns {string}
 */
function hexToTailwindColor(hex) {
  const [r, g, b] = hexRgb(hex, { format: 'array' })

  return r + ', ' + g + ', ' + b
}

export default hexToTailwindColor
