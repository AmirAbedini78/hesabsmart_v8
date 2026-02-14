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
import { ref } from 'vue'

/**
 * Composable for managing loading states.
 *
 * @param {boolean} [defaultValue=false] - The default loading state.
 * @returns {{ setLoading: (value?: boolean) => void, isLoading: import('vue').Ref<boolean> }}
 */
export function useLoader(defaultValue = false) {
  const isLoading = ref(defaultValue)

  /**
   * Sets the loading state.
   *
   * @param {boolean} [value=true] - The new loading state.
   * @returns {void}
   */
  function setLoading(value = true) {
    isLoading.value = value
  }

  return { setLoading, isLoading }
}
