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
import plugin from 'tailwindcss/plugin'

export default plugin(function ({ addUtilities }) {
  addUtilities({
    '.all-initial': { all: 'initial' },
    '.all-inherit': { all: 'inherit' },
    '.all-revert': { all: 'revert' },
    '.all-unset': { all: 'unset' },
  })
})
