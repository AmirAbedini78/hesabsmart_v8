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
import { useApp } from '@/Core/composables/useApp'

export function useVoip() {
  const { scriptConfig } = useApp()

  const voip = Innoclapps.app.config.globalProperties.$voip

  const hasVoIPClient = scriptConfig('voip.client') !== null

  return { voip, hasVoIPClient }
}
