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
import { computed } from 'vue'

import { useApp } from '@/Core/composables/useApp'

export function useSignature() {
  const { currentUser } = useApp()

  const signature = computed(() =>
    currentUser.value.mail_signature ? currentUser.value.mail_signature : ''
  )

  function addSignature(message = '') {
    return message + signature.value
  }

  return {
    addSignature,
    signature,
  }
}
